<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')->label(__('landivo.orders.number'))->searchable(),
                TextColumn::make('customer.name')->label(__('landivo.orders.customer'))->searchable(),
                TextColumn::make('status.name_ar')->label(__('landivo.orders.status'))->badge(),
                TextColumn::make('follow_up_at')
                    ->label('التذكير')
                    ->dateTime('Y-m-d H:i')
                    ->badge()
                    ->icon(fn ($record): ?string => $record->isFollowUpDue() ? 'heroicon-o-bell-alert' : ($record->hasPendingFollowUp() ? 'heroicon-o-clock' : null))
                    ->color(fn ($record): string => $record->isFollowUpDue() ? 'danger' : ($record->hasPendingFollowUp() ? 'warning' : 'gray'))
                    ->description(fn ($record): ?string => $record->isFollowUpDue() ? 'مستحق الآن' : ($record->follow_up_completed_at ? 'تمت المتابعة' : null))
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('total')->label(__('landivo.orders.total'))->sortable(),
                TextColumn::make('source')->label(__('landivo.orders.source')),
                TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Filter::make('due_follow_up')
                    ->label('تذكيرات مستحقة الآن')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('follow_up_at')
                        ->whereNull('follow_up_completed_at')
                        ->where('follow_up_at', '<=', now())),
                Filter::make('upcoming_follow_up')
                    ->label('متابعات قادمة')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('follow_up_at')
                        ->whereNull('follow_up_completed_at')
                        ->where('follow_up_at', '>', now())),
                Filter::make('completed_follow_up')
                    ->label('متابعات منجزة')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('follow_up_completed_at')),
            ])
            ->recordActions([
                Action::make('complete_follow_up')
                    ->label('تمت المتابعة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['follow_up_completed_at' => now()]))
                    ->visible(fn ($record): bool => $record->hasPendingFollowUp()),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
