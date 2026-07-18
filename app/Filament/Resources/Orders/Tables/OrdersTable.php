<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->label(__('landivo.orders.number'))->searchable(),
                TextColumn::make('customer.name')->label(__('landivo.orders.customer'))->searchable(),
                TextColumn::make('status.name_ar')->label(__('landivo.orders.status'))->badge(),
                TextColumn::make('total')->label(__('landivo.orders.total'))->sortable(),
                TextColumn::make('source')->label(__('landivo.orders.source')),
                TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable(),
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
