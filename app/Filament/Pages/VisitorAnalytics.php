<?php

namespace App\Filament\Pages;

use App\Models\LandingPage;
use App\Models\Product;
use App\Models\VisitorEvent;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class VisitorAnalytics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'تحليلات الزوار';

    protected static string|\UnitEnum|null $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'تحليلات الزوار';

    protected static ?string $slug = 'visitor-analytics';

    protected string $view = 'filament.pages.visitor-analytics';

    public string $period = '30';

    public array $stats = [];

    public array $topLandingPages = [];

    public array $topProducts = [];

    public array $dailyVisits = [];

    public array $currentVisitors = [];

    public function mount(): void
    {
        $this->loadAnalytics();
    }

    public function updatedPeriod(): void
    {
        $this->loadAnalytics();
    }

    public function refreshAnalytics(): void
    {
        $this->loadAnalytics();
    }

    private function loadAnalytics(): void
    {
        $accountId = auth()->user()?->account_id;
        $from = now()->subDays(max(1, (int) $this->period));
        $base = VisitorEvent::query()->where('account_id', $accountId)->where('event_type', 'page_view')->where('created_at', '>=', $from);
        $onlineFrom = now()->subMinutes(5);
        $onlineBase = VisitorEvent::query()->where('account_id', $accountId)->where('event_type', 'page_view')->where('created_at', '>=', $onlineFrom);

        $this->stats = [
            'visits' => (clone $base)->count(),
            'unique_visitors' => (clone $base)->whereNotNull('session_hash')->distinct()->count('session_hash'),
            'landing_pages' => (clone $base)->whereNotNull('landing_page_id')->distinct()->count('landing_page_id'),
            'products' => VisitorEvent::query()->where('account_id', $accountId)->where('event_type', 'product_view')->where('created_at', '>=', $from)->distinct()->count('product_id'),
            'online' => (clone $onlineBase)->whereNotNull('session_hash')->distinct()->count('session_hash'),
        ];

        $this->currentVisitors = (clone $onlineBase)
            ->latest('created_at')
            ->get(['session_hash', 'path', 'ip_address', 'user_agent', 'created_at'])
            ->unique('session_hash')
            ->take(10)
            ->map(fn (VisitorEvent $event): array => [
                'path' => $event->path ?: '/',
                'location' => $event->ip_address ? 'IP '.substr((string) $event->ip_address, 0, 18) : '—',
                'device' => str_contains(strtolower((string) $event->user_agent), 'mobile') ? 'Mobile' : 'Desktop',
                'last_seen' => $event->created_at?->diffForHumans() ?? '—',
            ])->values()->all();

        $this->topLandingPages = (clone $base)
            ->whereNotNull('landing_page_id')
            ->selectRaw('landing_page_id, COUNT(*) as visits')
            ->groupBy('landing_page_id')
            ->orderByDesc('visits')
            ->limit(8)
            ->get()
            ->map(function ($row): array {
                $page = LandingPage::with('translations')->find($row->landing_page_id);

                return ['name' => $page?->translations->firstWhere('locale', app()->getLocale())?->title ?? $page?->slug ?? '—', 'slug' => $page?->slug ?? '', 'visits' => (int) $row->visits];
            })->all();

        $this->topProducts = VisitorEvent::query()->where('account_id', $accountId)->where('event_type', 'product_view')->where('created_at', '>=', $from)
            ->whereNotNull('product_id')
            ->selectRaw('product_id, COUNT(*) as visits')
            ->groupBy('product_id')
            ->orderByDesc('visits')
            ->limit(8)
            ->get()
            ->map(function ($row): array {
                $product = Product::with('translations')->find($row->product_id);

                return ['name' => $product?->translations->firstWhere('locale', app()->getLocale())?->name ?? $product?->sku ?? '—', 'price' => $product?->price, 'visits' => (int) $row->visits];
            })->all();

        $this->dailyVisits = (clone $base)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as visits')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row): array => ['day' => Carbon::parse($row->day)->format('d/m'), 'visits' => (int) $row->visits])
            ->all();
    }
}
