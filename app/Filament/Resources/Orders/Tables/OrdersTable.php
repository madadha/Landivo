<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
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
                TextColumn::make('status.name_ar')->label(__('landivo.orders.status'))->badge()->extraAttributes(fn ($record): array => ['style' => 'background-color: '.($record->status?->color ?: '#64748b').';color:#fff']),
                TextColumn::make('landingPage.slug')->label('صفحة الهبوط')->toggleable(isToggledHiddenByDefault: true),
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
                Filter::make('created_between')->label('الفترة الزمنية')->form([
                    DatePicker::make('from')->label('من'),
                    DatePicker::make('until')->label('إلى'),
                ])->query(fn (Builder $query, array $data): Builder => $query->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('orders.created_at', '>=', $date))->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('orders.created_at', '<=', $date))),
                SelectFilter::make('order_status_id')->label('الحالة')->relationship('status', 'name_ar')->multiple(),
                SelectFilter::make('landing_page_id')->label('صفحة الهبوط')->relationship('landingPage', 'slug')->multiple(),
                SelectFilter::make('archived')->label('الأرشيف')->options(['0' => 'غير مؤرشفة', '1' => 'مؤرشفة'])->query(fn ($query, array $data) => $query->when(($data['value'] ?? null) === '1', fn ($q) => $q->whereNotNull('archived_at'))->when(($data['value'] ?? null) === '0', fn ($q) => $q->whereNull('archived_at'))),
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
                Action::make('archive')->label(fn ($record): string => $record->archived_at ? 'إلغاء الأرشفة' : 'أرشفة')->icon('heroicon-o-archive-box')->color(fn ($record): string => $record->archived_at ? 'success' : 'warning')->action(fn ($record) => $record->update(['archived_at' => $record->archived_at ? null : now()])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
