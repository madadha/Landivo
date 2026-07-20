<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('landivo.reviews.name'))->searchable(),
                TextColumn::make('rating')->label(__('landivo.reviews.rating'))->formatStateUsing(fn ($state): string => str_repeat('★', (int) $state))->color('warning')->sortable(),
                IconColumn::make('is_verified_purchase')->label('شراء موثق')->boolean(),
                TextColumn::make('order.order_number')->label('رقم الطلب')->searchable()->toggleable(),
                TextColumn::make('landingPage.slug')->label(__('landivo.reviews.landing_page'))->toggleable(),
                TextColumn::make('source')->label('المصدر')->badge()->formatStateUsing(fn ($state): string => match ($state) {
                    'order_link' => 'رابط طلب',
                    'landing_page' => 'صفحة هبوط',
                    default => 'الإدارة',
                }),
                ToggleColumn::make('is_approved')->label(__('landivo.reviews.approved')),
                ToggleColumn::make('is_featured')->label(__('landivo.reviews.featured')),
                TextColumn::make('created_at')->label(__('landivo.reviews.created_at'))->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')->label('حالة الموافقة'),
                TernaryFilter::make('is_verified_purchase')->label('شراء موثق'),
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
