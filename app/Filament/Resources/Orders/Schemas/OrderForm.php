<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الطلب والعميل')
                ->description('المعلومات الأساسية للطلب وبيانات التواصل مع العميل.')
                ->schema([
                    TextInput::make('order_number')->label('رقم الطلب')->required(),
                    Select::make('customer_id')->label('العميل')->relationship('customer', 'name')->searchable()->preload()->required(),
                    TextInput::make('customer_phone')
                        ->label('رقم الهاتف')
                        ->formatStateUsing(fn ($record): ?string => $record?->customer?->phone)
                        ->tel()
                        ->required()
                        ->maxLength(30)
                        ->helperText('يمكن تعديل الرقم هنا، وسيُحدّث في ملف العميل وروابط واتساب والفاتورة.'),
                    TextInput::make('customer_email')->label('البريد الإلكتروني')->formatStateUsing(fn ($record): ?string => $record?->customer?->email)->disabled()->dehydrated(false),
                    Select::make('order_status_id')->label('حالة الطلب')->relationship('status', 'name_ar')->required(),
                    TextInput::make('total')->label('الإجمالي')->numeric()->required(),
                    TextInput::make('currency')->label('العملة')->default('AED')->required(),
                    TextInput::make('source')->label('المصدر'),
                    TextInput::make('ip_address')->label('عنوان IP')->disabled()->dehydrated(false),
                    Textarea::make('user_agent')->label('الجهاز والمتصفح')->disabled()->dehydrated(false)->rows(2),
                    Textarea::make('notes')->label('ملاحظات الطلب')->rows(3)->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('بيانات الحملة الإعلانية')
                ->description('المصدر والحملة التسويقية المحفوظان تلقائيًا من رابط صفحة الهبوط.')
                ->icon('heroicon-o-megaphone')
                ->schema([
                    TextInput::make('campaign_source')
                        ->label('مصدر الإعلان / UTM Source')
                        ->formatStateUsing(fn ($record): ?string => data_get($record?->utm_parameters, 'utm_source') ?: $record?->source)
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('campaign_name')
                        ->label('الحملة الإعلانية / UTM Campaign')
                        ->formatStateUsing(fn ($record): ?string => data_get($record?->utm_parameters, 'utm_campaign'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('campaign_medium')
                        ->label('وسيلة الإعلان / UTM Medium')
                        ->formatStateUsing(fn ($record): ?string => data_get($record?->utm_parameters, 'utm_medium'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('campaign_content')
                        ->label('محتوى الإعلان / UTM Content')
                        ->formatStateUsing(fn ($record): ?string => data_get($record?->utm_parameters, 'utm_content'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('campaign_term')
                        ->label('الكلمة الإعلانية / UTM Term')
                        ->formatStateUsing(fn ($record): ?string => data_get($record?->utm_parameters, 'utm_term'))
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->columns(2),

            Section::make('التأجيل والتذكير')
                ->description('حدد موعدًا للعودة إلى العميل. سيظهر تنبيه واضح عند حلول الموعد فقط.')
                ->icon('heroicon-o-bell-alert')
                ->schema([
                    DateTimePicker::make('follow_up_at')
                        ->label('موعد المتابعة')
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('Y-m-d H:i')
                        ->helperText('مثال: إذا طلب العميل الاتصال يوم الاثنين، اختر يوم الاثنين والساعة المناسبة.'),
                    Textarea::make('follow_up_note')
                        ->label('سبب التأجيل أو رسالة التذكير')
                        ->placeholder('مثال: العميل طلب التواصل يوم الاثنين لتأكيد العنوان والكمية.')
                        ->rows(3),
                    TextInput::make('follow_up_state')
                        ->label('حالة التذكير')
                        ->formatStateUsing(function ($record): string {
                            if (! $record?->follow_up_at) {
                                return 'لا يوجد تذكير';
                            }

                            if ($record->follow_up_completed_at) {
                                return 'تمت المتابعة';
                            }

                            return $record->isFollowUpDue() ? 'مستحق الآن' : 'مجدول';
                        })
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->columns(2),

            Section::make('بيانات النموذج والعرض')
                ->schema([
                    KeyValue::make('form_data')->label('بيانات النموذج المرسلة')->columnSpanFull()->keyLabel('الحقل')->valueLabel('القيمة'),
                    Select::make('selected_offer')
                        ->label('العرض المختار')
                        ->formatStateUsing(fn ($record): ?string => data_get($record?->form_data, 'offer'))
                        ->options(function ($record): array {
                            $options = data_get(collect(data_get($record?->landingPage?->settings, 'order_form_fields', []))->firstWhere('internal_name', 'offer'), 'options', '');

                            if (is_array($options)) {
                                return collect($options)
                                    ->mapWithKeys(function (mixed $option): array {
                                        $value = is_array($option)
                                            ? (string) ($option['value'] ?? $option['label_ar'] ?? $option['label'] ?? '')
                                            : trim((string) $option);
                                        $label = is_array($option)
                                            ? (string) ($option['label_ar'] ?? $option['label'] ?? $value)
                                            : $value;

                                        return filled($value) ? [$value => $label] : [];
                                    })
                                    ->all();
                            }

                            return collect(preg_split('/\r\n|\r|\n/', (string) $options))
                                ->map(fn ($option): string => trim($option))
                                ->filter()
                                ->mapWithKeys(fn ($option): array => [$option => $option])
                                ->all();
                        }),
                ]),

            Section::make('سجل النشاط')
                ->description('سجل زمني لكل ما حدث على الطلب. يسجل النظام التغييرات تلقائيًا، ويمكنك إضافة مكالمة أو واتساب أو ملاحظة.')
                ->icon('heroicon-o-clock')
                ->schema([
                    Repeater::make('activities')
                        ->hiddenLabel()
                        ->relationship()
                        ->schema([
                            Hidden::make('user_id')->default(fn (): ?int => auth()->id()),
                            Select::make('type')
                                ->label('نوع النشاط')
                                ->options([
                                    'note' => 'ملاحظة',
                                    'call' => 'مكالمة',
                                    'whatsapp' => 'واتساب',
                                    'status' => 'تغيير حالة',
                                    'follow_up' => 'تأجيل أو متابعة',
                                    'update' => 'تحديث تلقائي',
                                    'system' => 'حدث من النظام',
                                ])
                                ->required()
                                ->default('note'),
                            TextInput::make('created_at_display')
                                ->label('التاريخ والوقت')
                                ->formatStateUsing(fn ($record): string => $record?->created_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'))
                                ->disabled()
                                ->dehydrated(false),
                            Textarea::make('body')->label('التفاصيل')->required()->rows(2)->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->collapsible()
                        ->itemLabel(fn (array $state): string => match ($state['type'] ?? 'note') {
                            'call' => 'مكالمة مع العميل',
                            'whatsapp' => 'رسالة واتساب',
                            'follow_up' => 'متابعة أو تأجيل',
                            'status' => 'تغيير حالة الطلب',
                            'system' => 'حدث من النظام',
                            'update' => 'تحديث تلقائي',
                            default => 'ملاحظة',
                        })
                        ->addActionLabel('إضافة نشاط جديد')
                        ->columnSpanFull(),
                ]),

            Section::make('ملفات وصور الطلب')
                ->schema([
                    Repeater::make('attachments')
                        ->hiddenLabel()
                        ->relationship()
                        ->schema([
                            FileUpload::make('path')->label('الملف')->disk('public')->directory('orders/attachments')->openable()->downloadable()->required(),
                            TextInput::make('original_name')->label('اسم الملف'),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('إرفاق ملف أو صورة')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
