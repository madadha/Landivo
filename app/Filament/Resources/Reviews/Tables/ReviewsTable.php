<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('landivo.reviews.name'))->searchable(),
                TextColumn::make('rating')->label(__('landivo.reviews.rating'))->sortable(),
                TextColumn::make('landingPage.slug')->label(__('landivo.reviews.landing_page'))->toggleable(),
                IconColumn::make('is_approved')->label(__('landivo.reviews.approved'))->boolean(),
                IconColumn::make('is_featured')->label(__('landivo.reviews.featured'))->boolean(),
                TextColumn::make('created_at')->label(__('landivo.reviews.created_at'))->dateTime()->sortable(),
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
