<?php

namespace App\Filament\Pages;

use App\Jobs\ProcessDataExport;
use App\Jobs\ProcessDataImport;
use App\Models\DataTransfer;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class DataTransferCenter extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsUpDown;

    protected static ?string $navigationLabel = 'مركز الاستيراد والتصدير';

    protected static string|\UnitEnum|null $navigationGroup = 'التقارير والتحليلات';

    protected static ?int $navigationSort = 60;

    protected static ?string $title = 'مركز الاستيراد والتصدير';

    protected static ?string $slug = 'data-transfers';

    protected string $view = 'filament.pages.data-transfer-center';

    public string $operation = 'export';

    public string $entity = 'orders';

    public TemporaryUploadedFile|string|null $importFile = null;

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->check()) {
            return null;
        }

        $count = DataTransfer::query()
            ->where('account_id', auth()->user()->account_id)
            ->whereIn('status', ['queued', 'processing'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public function updatedOperation(): void
    {
        if ($this->operation === 'import' && $this->entity === 'orders') {
            $this->entity = 'customers';
        }
        $this->resetValidation();
    }

    public function startTransfer(): void
    {
        $this->validate([
            'operation' => ['required', 'in:import,export'],
            'entity' => ['required', $this->operation === 'import' ? 'in:customers,products' : 'in:orders,customers,products'],
            'importFile' => $this->operation === 'import'
                ? ['required', 'file', 'mimes:csv,txt', 'max:51200']
                : ['nullable'],
        ], [
            'importFile.required' => 'اختر ملف CSV أولًا.',
            'importFile.mimes' => 'الملف يجب أن يكون CSV.',
            'importFile.max' => 'الحد الأقصى للملف 50 MB.',
        ]);

        $accountId = (int) auth()->user()->account_id;
        $sourcePath = null;
        $originalName = null;

        if ($this->operation === 'import' && $this->importFile instanceof TemporaryUploadedFile) {
            $originalName = $this->importFile->getClientOriginalName();
            $sourcePath = $this->importFile->storeAs(
                "data-transfers/{$accountId}/imports",
                Str::uuid().'.csv',
                'local',
            );
        }

        $transfer = DataTransfer::query()->create([
            'account_id' => $accountId,
            'user_id' => auth()->id(),
            'type' => $this->operation,
            'entity' => $this->entity,
            'status' => 'queued',
            'source_path' => $sourcePath,
            'original_name' => $originalName,
        ]);

        if ($this->operation === 'import') {
            ProcessDataImport::dispatch($transfer->getKey());
        } else {
            ProcessDataExport::dispatch($transfer->getKey());
        }

        $this->importFile = null;

        Notification::make()
            ->title($this->operation === 'import' ? 'بدأ الاستيراد' : 'بدأ التصدير')
            ->body('ستتحدث حالة العملية تلقائيًا دون تعطيل لوحة التحكم.')
            ->success()
            ->send();
    }

    public function clearHistory(): void
    {
        $transfers = DataTransfer::query()
            ->where('account_id', auth()->user()?->account_id)
            ->whereIn('status', ['completed', 'failed'])
            ->get();

        $transfers->each(fn (DataTransfer $transfer) => $this->deleteTransferFiles($transfer));
        DataTransfer::query()->whereKey($transfers->modelKeys())->delete();

        Notification::make()
            ->title('تم تنظيف سجل العمليات')
            ->body("حُذفت {$transfers->count()} عملية مع ملفاتها المؤقتة والنتائج التابعة لها.")
            ->success()
            ->send();
    }

    public function deleteTransfer(int $transferId): void
    {
        $transfer = DataTransfer::query()
            ->where('account_id', auth()->user()?->account_id)
            ->findOrFail($transferId);

        if ($transfer->isRunning()) {
            Notification::make()
                ->title('لا يمكن حذف عملية جارية')
                ->body('انتظر حتى تكتمل العملية أو تفشل، ثم أعد المحاولة.')
                ->warning()
                ->send();

            return;
        }

        $this->deleteTransferFiles($transfer);
        $transfer->delete();

        Notification::make()
            ->title('تم حذف العملية')
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        return [
            'transfers' => DataTransfer::query()
                ->where('account_id', auth()->user()?->account_id)
                ->with('user:id,name')
                ->latest()
                ->limit(30)
                ->get(),
        ];
    }

    public function downloadUrl(DataTransfer $transfer): string
    {
        return route('data-transfers.download', $transfer);
    }

    public function templateUrl(string $entity): string
    {
        return route('data-transfers.template', ['entity' => $entity]);
    }

    private function deleteTransferFiles(DataTransfer $transfer): void
    {
        Storage::disk('local')->delete(array_filter([
            $transfer->source_path,
            $transfer->result_path,
        ]));
    }
}
