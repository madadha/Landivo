<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Orders\OrderResource;
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
        $query = Order::query()->where('account_id', $accountId);
        $total = (clone $query)->count();
        $revenue = (float) (clone $query)->sum('total');

        $rows = OrderStatus::query()
            ->where('account_id', $accountId)
            ->withCount(['orders as orders_count' => fn (Builder $q) => $q->where('account_id', $accountId)])
            ->withSum(['orders as revenue_total' => fn (Builder $q) => $q->where('account_id', $accountId)], 'total')
            ->orderBy('sort_order')
            ->get();

        $averageOrderValue = $total > 0 ? $revenue / $total : 0;

        return compact('rows', 'total', 'revenue', 'averageOrderValue');
    }

    public function getOrdersUrl(?int $statusId = null): string
    {
        if (! $statusId) {
            return OrderResource::getUrl('index');
        }

        return OrderResource::getUrl('index', [
            'filters' => [
                'order_status_id' => [
                    'values' => [(string) $statusId],
                ],
            ],
        ]);
    }
}
