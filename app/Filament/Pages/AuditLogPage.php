<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;

class AuditLogPage extends Page
{
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'سجل التدقيق';

    protected static string|\UnitEnum|null $navigationGroup = 'إدارة النظام';

    protected static ?int $navigationSort = 40;

    protected static ?string $title = 'سجل التدقيق';

    protected static ?string $slug = 'audit-log';

    protected string $view = 'filament.pages.audit-log';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $event = '';

    public string $module = '';

    public string $userId = '';

    public string $search = '';

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(29)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['dateFrom', 'dateTo', 'event', 'module', 'userId', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->dateFrom = now()->subDays(29)->toDateString();
        $this->dateTo = now()->toDateString();
        $this->event = '';
        $this->module = '';
        $this->userId = '';
        $this->search = '';
        $this->resetPage();
    }

    protected function getViewData(): array
    {
        $query = $this->filteredQuery();
        $total = (clone $query)->count();

        $events = (clone $query)
            ->selectRaw('event, COUNT(*) AS events_count')
            ->groupBy('event')
            ->pluck('events_count', 'event');

        $moduleBreakdown = (clone $query)
            ->selectRaw('module, COUNT(*) AS events_count')
            ->groupBy('module')
            ->orderByDesc('events_count')
            ->limit(8)
            ->get();

        $dailyActivity = (clone $query)
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS events_count')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row): array => ['day' => Carbon::parse($row->day)->format('d/m'), 'count' => (int) $row->events_count]);

        return [
            'logs' => (clone $query)->with('user')->latest()->paginate(25),
            'total' => $total,
            'createdCount' => (int) ($events['created'] ?? 0),
            'updatedCount' => (int) ($events['updated'] ?? 0),
            'deletedCount' => (int) ($events['deleted'] ?? 0),
            'authCount' => (int) (($events['login'] ?? 0) + ($events['logout'] ?? 0)),
            'moduleBreakdown' => $moduleBreakdown,
            'dailyActivity' => $dailyActivity,
            'maxDailyActivity' => max(1, (int) $dailyActivity->max('count')),
            'modules' => AuditLog::query()->where('account_id', $this->accountId())->distinct()->orderBy('module')->pluck('module'),
            'users' => User::query()->where('account_id', $this->accountId())->orderBy('name')->get(['id', 'name', 'email']),
        ];
    }

    private function filteredQuery(): Builder
    {
        return AuditLog::query()
            ->where('account_id', $this->accountId())
            ->when($this->dateFrom, fn (Builder $query) => $query->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($this->dateTo, fn (Builder $query) => $query->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay()))
            ->when($this->event, fn (Builder $query) => $query->where('event', $this->event))
            ->when($this->module, fn (Builder $query) => $query->where('module', $this->module))
            ->when($this->userId, fn (Builder $query) => $query->where('user_id', $this->userId))
            ->when($this->search, function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $nested) use ($term): void {
                    $nested->where('description', 'like', $term)
                        ->orWhere('subject_label', 'like', $term)
                        ->orWhere('ip_address', 'like', $term)
                        ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', $term)->orWhere('email', 'like', $term));
                });
            });
    }

    private function accountId(): ?int
    {
        return auth()->user()?->account_id;
    }
}
