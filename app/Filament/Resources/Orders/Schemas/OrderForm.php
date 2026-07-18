<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('order_number')->label('رقم الطلب / Order number')->required(),
            Select::make('customer_id')->label('العميل / Customer')->relationship('customer', 'name')->searchable()->preload()->required(),
            TextInput::make('customer_phone')->label('رقم الهاتف / Phone')->formatStateUsing(fn ($record): ?string => $record?->customer?->phone)->disabled()->dehydrated(false),
            TextInput::make('customer_email')->label('البريد الإلكتروني / Email')->formatStateUsing(fn ($record): ?string => $record?->customer?->email)->disabled()->dehydrated(false),
            Select::make('order_status_id')->label('حالة الطلب / Status')->relationship('status', 'name_ar')->required(),
            TextInput::make('total')->label('الإجمالي / Total')->numeric()->required(),
            TextInput::make('currency')->label('العملة / Currency')->default('AED')->required(),
            TextInput::make('source')->label('المصدر / Source'),
            TextInput::make('ip_address')->label('IP address')->disabled()->dehydrated(false),
            Textarea::make('user_agent')->label('الجهاز والمتصفح / User agent')->disabled()->dehydrated(false)->rows(2),
            Textarea::make('notes')->label('ملاحظات / Notes')->columnSpanFull(),
            KeyValue::make('form_data')->label('بيانات النموذج / Submitted form data')->columnSpanFull()->keyLabel('اسم الحقل / Field')->valueLabel('القيمة / Value'),
            Select::make('selected_offer')
                ->label('العرض المختار / Selected offer')
                ->formatStateUsing(fn ($record): ?string => data_get($record?->form_data, 'offer'))
                ->options(function ($record): array {
                    $options = data_get(collect(data_get($record?->landingPage?->settings, 'order_form_fields', []))->firstWhere('internal_name', 'offer'), 'options', '');

                    return collect(preg_split('/\r\n|\r|\n/', (string) $options))
                        ->map(fn ($option): string => trim($option))
                        ->filter()
                        ->mapWithKeys(fn ($option): array => [$option => $option])
                        ->all();
                }),
            Repeater::make('activities')->label('سجل النشاط والملاحظات / Activity log')->relationship()->schema([
                Select::make('type')->label('النوع / Type')->options(['note' => 'ملاحظة', 'call' => 'مكالمة', 'whatsapp' => 'واتساب', 'status' => 'تغيير حالة'])->required()->default('note'),
                Textarea::make('body')->label('التفاصيل / Details')->required()->rows(2)->columnSpanFull(),
            ])->columns(2)->addActionLabel('إضافة نشاط')->columnSpanFull(),
            Repeater::make('attachments')->label('ملفات وصور الطلب / Order files & images')->relationship()->schema([
                FileUpload::make('path')->label('الملف / File')->disk('public')->directory('orders/attachments')->openable()->downloadable()->required(),
                TextInput::make('original_name')->label('اسم الملف / File name'),
            ])->columns(2)->addActionLabel('إرفاق ملف')->columnSpanFull(),
        ]);
    }
}
