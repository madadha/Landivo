<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\LandingPage;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class LeadsOverview extends StatsOverviewWidget
{
    protected ?string $heading = null;

    protected function getHeading(): ?string
    {
        return __('landivo.dashboard.heading');
    }

    protected function getStats(): array
    {
        $accountId = auth()->user()?->account_id;

        return [
            Stat::make(__('landivo.dashboard.total_leads'), Customer::where('account_id', $accountId)->count())->description(__('landivo.dashboard.captured'))->color('primary'),
            Stat::make(__('landivo.dashboard.orders'), Order::where('account_id', $accountId)->count())->description(__('landivo.dashboard.submitted'))->color('success'),
            Stat::make(__('landivo.dashboard.new_orders'), Order::where('account_id', $accountId)->whereHas('status', fn ($query) => $query->where('slug', 'new'))->count())->description(__('landivo.dashboard.awaiting_contact'))->color('warning'),
            Stat::make(__('landivo.dashboard.revenue'), number_format((float) Order::where('account_id', $accountId)->sum('total'), 2).' USD')->description(__('landivo.dashboard.order_value'))->color('success'),
            Stat::make(__('landivo.dashboard.landing_pages'), LandingPage::where('account_id', $accountId)->count())->description(__('landivo.dashboard.created_pages'))->color('info'),
        ];
    }
}
