<?php

namespace App\Filament\Resources\LandingPages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LandingPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')->label(__('landivo.landing_pages.slug'))->searchable(),
                TextColumn::make('template')->label(__('landivo.landing_pages.template')),
                TextColumn::make('status')->label(__('landivo.landing_pages.status'))->badge(),
                TextColumn::make('default_locale')->label(__('landivo.landing_pages.default_locale')),
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
