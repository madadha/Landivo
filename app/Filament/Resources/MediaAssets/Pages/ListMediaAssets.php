<?php

namespace App\Filament\Resources\MediaAssets\Pages;

use App\Filament\Resources\MediaAssets\MediaAssetResource;
use App\Filament\Widgets\MediaLibraryStats;
use App\Services\MediaLibraryService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMediaAssets extends ListRecords
{
    protected static string $resource = MediaAssetResource::class;

    public function mount(): void
    {
        app(MediaLibraryService::class)->synchronizeAccount((int) auth()->user()?->account_id);
        parent::mount();
    }

    protected function getHeaderWidgets(): array
    {
        return [MediaLibraryStats::class];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('رفع ملف جديد')->icon('heroicon-o-arrow-up-tray'),
            Action::make('scan')->label('فحص التخزين والاستخدام')->icon('heroicon-o-arrow-path')->action(function (): void {
                $result = app(MediaLibraryService::class)->synchronizeAccount((int) auth()->user()?->account_id, true);
                Notification::make()->title('اكتمل فحص الوسائط')->body("الإجمالي: {$result['total']} — مستخدم: {$result['used']} — غير مستخدم: {$result['unused']}")->success()->send();
            }),
            Action::make('cleanup')->label('تنظيف غير المستخدم')->icon('heroicon-o-sparkles')->color('danger')->requiresConfirmation()->modalHeading('تنظيف الملفات غير المستخدمة')->modalDescription('سيتم إعادة فحص جميع الاستخدامات أولًا، ثم حذف الملفات غير المرتبطة بأي منتج أو صفحة أو إعداد. لا يمكن التراجع عن الحذف.')->action(function (): void {
                $result = app(MediaLibraryService::class)->cleanUnused((int) auth()->user()?->account_id);
                Notification::make()->title('اكتمل تنظيف التخزين')->body("تم حذف {$result['count']} ملف وتحرير ".$this->formatBytes((int) $result['bytes']))->success()->send();
            }),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 1).' MB';
    }
}
