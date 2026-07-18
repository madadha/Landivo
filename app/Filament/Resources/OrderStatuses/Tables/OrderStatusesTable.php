<?php

namespace App\Filament\Resources\OrderStatuses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                TextColumn::make('name_ar')->label(__('landivo.order_statuses.name_ar'))->searchable(),
                TextColumn::make('name_en')->label(__('landivo.order_statuses.name_en'))->searchable(),
                TextColumn::make('slug')->label(__('landivo.order_statuses.slug')),
                TextColumn::make('color')->label(__('landivo.order_statuses.color')),
                IconColumn::make('is_active')->label(__('landivo.order_statuses.is_active'))->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
