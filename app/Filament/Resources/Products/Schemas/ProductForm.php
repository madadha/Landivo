<?php

namespace App\Filament\Resources\Products\Schemas;

use App\ProductStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')->label(__('landivo.products.sku'))->maxLength(100),
                TextInput::make('price')->label(__('landivo.products.price'))->numeric()->required()->default(0),
                TextInput::make('compare_at_price')->label(__('landivo.products.compare_at_price'))->numeric(),
                TextInput::make('currency')->label(__('landivo.products.currency'))->required()->default('USD')->maxLength(3),
                TextInput::make('quantity')->label(__('landivo.products.quantity'))->numeric()->integer()->required()->default(0),
                Select::make('status')->label(__('landivo.products.status'))->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status): array => [$status->value => $status->label()])->all())->required()->default(ProductStatus::Draft->value),
                Section::make(__('landivo.products.image'))->schema([
                    FileUpload::make('primary_image_path')->label(__('landivo.products.image'))->image()->disk('public')->directory('products')->visibility('public')->openable()->downloadable(),
                    FileUpload::make('metadata.image_ar')->label('Product image (Arabic)')->image()->disk('public')->directory('products')->visibility('public')->openable()->downloadable(),
                    FileUpload::make('metadata.image_en')->label('Product image (English)')->image()->disk('public')->directory('products')->visibility('public')->openable()->downloadable()->visible(fn (Get $get): bool => collect($get('translations') ?? [])->contains('locale', 'en')),
                ])->columns(2),
                Repeater::make('translations')
                    ->label(__('landivo.products.translations'))
                    ->relationship()
                    ->schema([
                        Select::make('locale')->label(__('landivo.products.locale'))->options(__('landivo.locales'))->required(),
                        TextInput::make('name')->label(__('landivo.products.name'))->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
