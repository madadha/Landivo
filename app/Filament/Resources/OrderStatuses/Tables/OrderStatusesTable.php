<?php

namespace App\Filament\Resources\OrderStatuses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')->label(__('landivo.order_statuses.name_ar'))->searchable()->badge()->extraAttributes(fn ($record): array => ['style' => 'background-color: '.($record->color ?: '#64748b').';color:#fff']),
                TextColumn::make('name_en')->label(__('landivo.order_statuses.name_en'))->searchable(),
                TextColumn::make('slug')->label(__('landivo.order_statuses.slug')),
                TextColumn::make('color')->label(__('landivo.order_statuses.color'))->copyable(),
                IconColumn::make('is_active')->label(__('landivo.order_statuses.is_active'))->boolean(),
                IconColumn::make('archived_at')->label('مؤرشف')->boolean()->state(fn ($record): bool => filled($record->archived_at)),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('archived')->label('الأرشيف')->options(['0' => 'نشطة', '1' => 'مؤرشفة'])->query(fn ($query, array $data) => $query->when(($data['value'] ?? null) === '1', fn ($q) => $q->whereNotNull('archived_at'))->when(($data['value'] ?? null) === '0', fn ($q) => $q->whereNull('archived_at'))),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('archive')->label(fn ($record): string => $record->archived_at ? 'إلغاء الأرشفة' : 'أرشفة')->icon('heroicon-o-archive-box')->color(fn ($record): string => $record->archived_at ? 'success' : 'warning')->action(fn ($record) => $record->update(['archived_at' => $record->archived_at ? null : now()])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
