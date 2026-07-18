<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primary_image_path')->label(__('landivo.products.image'))->disk('public')->square()->size(56),
                TextColumn::make('sku')->label(__('landivo.products.sku'))->searchable(),
                TextColumn::make('price')->label(__('landivo.products.price'))->sortable(),
                TextColumn::make('quantity')->label(__('landivo.products.quantity'))->sortable(),
                TextColumn::make('status')->label(__('landivo.products.status'))->badge(),
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
