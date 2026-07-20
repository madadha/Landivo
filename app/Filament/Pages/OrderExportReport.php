<?php

namespace App\Filament\Pages;

use App\Models\LandingPage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;

class OrderExportReport extends Page
{
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'تصدير الطلبات';

    protected static string|\UnitEnum|null $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'تصدير الطلبات';

    protected static ?string $slug = 'order-export-report';

    protected string $view = 'filament.pages.order-export-report';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $statusId = '';

    public string $landingPageId = '';

    public string $productId = '';

    public string $source = '';

    public string $search = '';

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(29)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['dateFrom', 'dateTo', 'statusId', 'landingPageId', 'productId', 'source', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->dateFrom = now()->subDays(29)->toDateString();
        $this->dateTo = now()->toDateString();
        $this->statusId = '';
        $this->landingPageId = '';
        $this->productId = '';
        $this->source = '';
        $this->search = '';
        $this->resetPage();
    }

    protected function getViewData(): array
    {
        $query = $this->filteredQuery();
        $ordersCount = (clone $query)->count();
        $salesTotal = (float) (clone $query)->sum('total');

        $statusBreakdown = (clone $query)
            ->selectRaw('order_status_id, COUNT(*) AS orders_count, COALESCE(SUM(total), 0) AS sales_total')
            ->with('status')
            ->groupBy('order_status_id')
            ->orderByDesc('orders_count')
            ->get();

        $topProducts = OrderItem::query()
            ->whereIn('order_id', (clone $query)->select('orders.id'))
            ->selectRaw('product_name, SUM(quantity) AS units_count, COALESCE(SUM(total), 0) AS sales_total')
            ->groupBy('product_name')
            ->orderByDesc('sales_total')
            ->limit(6)
            ->get();

        $landingBreakdown = (clone $query)
            ->whereNotNull('landing_page_id')
            ->selectRaw('landing_page_id, COUNT(*) AS orders_count, COALESCE(SUM(total), 0) AS sales_total')
            ->with('landingPage.translations')
            ->groupBy('landing_page_id')
            ->orderByDesc('orders_count')
            ->limit(6)
            ->get();

        $sourceBreakdown = (clone $query)
            ->selectRaw("COALESCE(NULLIF(source, ''), 'direct') AS source_name, COUNT(*) AS orders_count, COALESCE(SUM(total), 0) AS sales_total")
            ->groupBy('source_name')
            ->orderByDesc('orders_count')
            ->limit(6)
            ->get();

        return [
            'orders' => (clone $query)
                ->with(['customer', 'status', 'landingPage.translations', 'items'])
                ->latest('orders.created_at')
                ->paginate(20),
            'ordersCount' => $ordersCount,
            'salesTotal' => $salesTotal,
            'averageOrderValue' => $ordersCount > 0 ? $salesTotal / $ordersCount : 0,
            'customersCount' => (clone $query)->whereNotNull('customer_id')->distinct()->count('customer_id'),
            'statusBreakdown' => $statusBreakdown,
            'topProducts' => $topProducts,
            'landingBreakdown' => $landingBreakdown,
            'sourceBreakdown' => $sourceBreakdown,
            'statuses' => OrderStatus::query()->where('account_id', $this->accountId())->orderBy('sort_order')->get(),
            'landingPages' => LandingPage::query()->where('account_id', $this->accountId())->with('translations')->latest()->get(),
            'products' => Product::query()->where('account_id', $this->accountId())->with('translations')->latest()->get(),
            'sources' => Order::query()->where('account_id', $this->accountId())->whereNotNull('source')->where('source', '!=', '')->distinct()->orderBy('source')->pluck('source'),
        ];
    }

    public function exportUrl(): string
    {
        return route('reports.orders.export', array_filter([
            'date_from' => $this->dateFrom, 'date_to' => $this->dateTo, 'status_id' => $this->statusId,
            'landing_page_id' => $this->landingPageId, 'product_id' => $this->productId,
            'source' => $this->source, 'search' => $this->search,
        ], fn (mixed $value): bool => filled($value)));
    }

    private function filteredQuery(): Builder
    {
        return Order::query()
            ->where('orders.account_id', $this->accountId())
            ->when($this->dateFrom, fn (Builder $query) => $query->where('orders.created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($this->dateTo, fn (Builder $query) => $query->where('orders.created_at', '<=', Carbon::parse($this->dateTo)->endOfDay()))
            ->when($this->statusId, fn (Builder $query) => $query->where('order_status_id', $this->statusId))
            ->when($this->landingPageId, fn (Builder $query) => $query->where('landing_page_id', $this->landingPageId))
            ->when($this->productId, fn (Builder $query) => $query->whereHas('items', fn (Builder $items) => $items->where('product_id', $this->productId)))
            ->when($this->source, fn (Builder $query) => $query->where('source', $this->source))
            ->when($this->search, function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $search) use ($term): void {
                    $search->where('order_number', 'like', $term)
                        ->orWhereHas('customer', fn (Builder $customer) => $customer
                            ->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhere('email', 'like', $term));
                });
            });
    }

    private function accountId(): ?int
    {
        return auth()->user()?->account_id;
    }

    private function landingPageName(?LandingPage $page): string
    {
        return $page?->translations?->firstWhere('locale', 'ar')?->title
            ?? $page?->translations?->first()?->title
            ?? $page?->slug
            ?? '';
    }
}
