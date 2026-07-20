<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label(__('landivo.reviews.name'))->required()->maxLength(150),
                Select::make('rating')->label(__('landivo.reviews.rating'))->options([1 => '1 / 5', 2 => '2 / 5', 3 => '3 / 5', 4 => '4 / 5', 5 => '5 / 5'])->required()->default(5),
                Select::make('landing_page_id')->label(__('landivo.reviews.landing_page'))->relationship('landingPage', 'slug')->searchable()->preload(),
                Select::make('product_id')->label(__('landivo.reviews.product'))->relationship('product', 'sku')->searchable()->preload(),
                Select::make('order_id')->label('الطلب / Order')->relationship('order', 'order_number')->searchable()->preload(),
                RichEditor::make('content')
                    ->label(__('landivo.reviews.content'))
                    ->helperText('اختياري — يمكن حفظ التقييم بالنجوم فقط / Optional — a star-only review can be saved.')
                    ->dehydrateStateUsing(fn (?string $state): string => $state ?? '')
                    ->columnSpanFull(),
                FileUpload::make('photo_path')->label(__('landivo.reviews.photo'))->image()->disk('public')->directory('reviews'),
                Toggle::make('is_verified_purchase')->label('عملية شراء موثقة / Verified purchase')->default(false),
                Toggle::make('is_approved')->label(__('landivo.reviews.approved'))->default(false),
                Toggle::make('is_featured')->label(__('landivo.reviews.featured'))->default(false),
            ]);
    }
}
