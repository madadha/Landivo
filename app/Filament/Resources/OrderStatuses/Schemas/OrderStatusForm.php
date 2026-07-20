<?php

namespace App\Filament\Resources\OrderStatuses\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')->label(__('landivo.order_statuses.name_ar'))->required(),
                TextInput::make('name_en')->label(__('landivo.order_statuses.name_en'))->required(),
                TextInput::make('slug')->label(__('landivo.order_statuses.slug'))->required()->alphaDash(),
                ColorPicker::make('color')->label(__('landivo.order_statuses.color'))->default('#64748b'),
                TextInput::make('sort_order')->label(__('landivo.order_statuses.sort_order'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('landivo.order_statuses.is_active'))->default(true),
                Toggle::make('is_final')->label(__('landivo.order_statuses.is_final')),
                Toggle::make('deduct_inventory')
                    ->label('خصم المخزون عند هذه الحالة')
                    ->helperText('فعّلها لحالة «تم التسليم». عند انتقال الطلب إليها سيُخصم مخزون صفحة الهبوط والمنتج والمتغير مرة واحدة فقط.')
                    ->default(false),
            ]);
    }
}
