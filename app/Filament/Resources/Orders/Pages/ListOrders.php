<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $accountId = auth()->user()?->account_id;
        $dueCount = $this->getModel()::query()
            ->where('account_id', $accountId)
            ->whereNotNull('follow_up_at')
            ->whereNull('follow_up_completed_at')
            ->where('follow_up_at', '<=', now())
            ->count();

        return [
            'all' => Tab::make('كل الطلبات')->icon('heroicon-o-queue-list'),
            'due' => Tab::make('تذكيرات مستحقة')
                ->icon('heroicon-o-bell-alert')
                ->badge($dueCount ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereNotNull('follow_up_at')
                    ->whereNull('follow_up_completed_at')
                    ->where('follow_up_at', '<=', now())),
            'upcoming' => Tab::make('متابعات قادمة')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereNotNull('follow_up_at')
                    ->whereNull('follow_up_completed_at')
                    ->where('follow_up_at', '>', now())),
            'completed' => Tab::make('تمت المتابعة')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotNull('follow_up_completed_at')),
        ];
    }
}
