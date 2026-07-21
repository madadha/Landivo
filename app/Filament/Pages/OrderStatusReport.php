<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\OrderStatus;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class OrderStatusReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?string $navigationLabel = 'تقرير حالات الطلبات';
    protected static string|\UnitEnum|null $navigationGroup = 'التقارير';
    protected static ?int $navigationSort = 15;
    protected static ?string $title = 'تقرير حالات الطلبات';
    protected static ?string $slug = 'order-status-report';
    protected string $view = 'filament.pages.order-status-report';

    protected function getViewData(): array
    {
        $accountId = auth()->user()?->account_id;

        $landingPages = LandingPage::query()
            ->where('account_id', $accountId)
            ->with('translations:id,landing_page_id,locale,title')
            ->orderBy('slug')
            ->get();

        $selectedLandingPageId = request()->integer('landing_page') ?: null;
        if ($selectedLandingPageId && ! $landingPages->contains('id', $selectedLandingPageId)) {
            $selectedLandingPageId = null;
        }

        $statusRows = OrderStatus::query()
            ->where('account_id', $accountId)
            ->orderBy('sort_order')
            ->get();

        $selectedStatusId = request()->integer('status') ?: null;
        if ($selectedStatusId && ! $statusRows->contains('id', $selectedStatusId)) {
            $selectedStatusId = null;
        }

        $landingPageScope = fn (Builder $query): Builder => $query
            ->when($selectedLandingPageId, fn (Builder $builder, int $landingPageId): Builder => $builder->where('landing_page_id', $landingPageId));

        $filteredQuery = Order::query()
            ->where('account_id', $accountId)
            ->when($selectedLandingPageId, fn (Builder $builder, int $landingPageId): Builder => $builder->where('landing_page_id', $landingPageId))
            ->when($selectedStatusId, fn (Builder $builder, int $statusId): Builder => $builder->where('order_status_id', $statusId));

        $total = (clone $filteredQuery)->count();
        $revenue = (float) (clone $filteredQuery)->sum('total');
        $distributionTotal = Order::query()
            ->where('account_id', $accountId)
            ->when($selectedLandingPageId, fn (Builder $builder, int $landingPageId): Builder => $builder->where('landing_page_id', $landingPageId))
            ->count();

        $rows = OrderStatus::query()
            ->where('account_id', $accountId)
            ->withCount(['orders as orders_count' => fn (Builder $q) => $landingPageScope($q->where('account_id', $accountId))])
            ->withSum(['orders as revenue_total' => fn (Builder $q) => $landingPageScope($q->where('account_id', $accountId))], 'total')
            ->orderBy('sort_order')
            ->get();

        $selectionOrders = Order::query()
            ->where('account_id', $accountId)
            ->when($selectedStatusId, fn (Builder $builder, int $statusId): Builder => $builder->where('order_status_id', $statusId))
            ->when($selectedLandingPageId, fn (Builder $builder, int $landingPageId): Builder => $builder->where('landing_page_id', $landingPageId))
            ->with(['customer:id,name,phone,email', 'status:id,name_ar,name_en,color', 'landingPage.translations:id,landing_page_id,locale,title'])
            ->latest('created_at')
            ->get();

        $averageOrderValue = $total > 0 ? $revenue / $total : 0;

        return compact('rows', 'landingPages', 'total', 'distributionTotal', 'revenue', 'averageOrderValue', 'selectedStatusId', 'selectedLandingPageId', 'selectionOrders');
    }

    public function getOrdersUrl(?int $statusId = null, ?int $landingPageId = null): string
    {
        if (! $statusId && ! $landingPageId) {
            return OrderResource::getUrl('index');
        }

        $filters = [];
        if ($statusId) {
            $filters['order_status_id'] = ['values' => [(string) $statusId]];
        }
        if ($landingPageId) {
            $filters['landing_page_id'] = ['values' => [(string) $landingPageId]];
        }

        return OrderResource::getUrl('index', [
            'filters' => $filters,
        ]);
    }
}
