<?php

namespace App\Filament\Resources\Products\Schemas;

use App\ProductStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                Section::make('وسائط المنتج / Product Media')
                    ->description('أضف عدة صور أو فيديوهات أو ملفات، وحدد لغة كل وسيط وترتيبه. اترك اللغة فارغة ليظهر في العربية والإنجليزية.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Repeater::make('media')
                            ->hiddenLabel()
                            ->relationship()
                            ->schema([
                                Select::make('locale')
                                    ->label('لغة العرض / Display language')
                                    ->options(['ar' => 'العربية', 'en' => 'English'])
                                    ->placeholder('كل اللغات / All languages'),
                                Select::make('media_type')
                                    ->label('نوع الوسيط / Media type')
                                    ->options([
                                        'image' => 'صورة / Image',
                                        'video' => 'فيديو / Video',
                                        'document' => 'ملف / Document',
                                    ])
                                    ->default('image')
                                    ->required(),
                                FileUpload::make('file_path')
                                    ->label('الملف / File')
                                    ->disk('public')
                                    ->directory('products/media')
                                    ->acceptedFileTypes(['image/*', 'video/*', 'application/pdf'])
                                    ->maxSize(51200)
                                    ->previewable()
                                    ->openable()
                                    ->downloadable()
                                    ->columnSpanFull(),
                                TextInput::make('external_url')
                                    ->label('رابط فيديو أو وسيط خارجي / External media URL')
                                    ->url()
                                    ->placeholder('https://www.youtube.com/watch?v=...')
                                    ->columnSpanFull(),
                                TextInput::make('title')->label('العنوان / Title')->maxLength(255),
                                TextInput::make('alt_text')->label('النص البديل / Alt text')->maxLength(255),
                                Textarea::make('caption')->label('الوصف أسفل الوسيط / Caption')->rows(2)->columnSpanFull(),
                                TextInput::make('sort_order')->label('الترتيب / Sort order')->numeric()->integer()->minValue(0)->default(0),
                                Toggle::make('is_active')->label('ظاهر / Visible')->default(true),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['title'] ?? $state['file_path'] ?? 'وسيط جديد / New media')
                            ->addActionLabel('إضافة صورة أو وسيط / Add media'),
                    ])
                    ->collapsible(),
                Section::make('خيارات المنتج / Product Options')
                    ->description('عرّف الخيارات العامة مثل الحجم والوزن واللون. استخدم رمزًا ثابتًا لكل قيمة ليسهل إنشاء المتغيرات.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Repeater::make('options')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('name_ar')->label('اسم الخيار بالعربية')->placeholder('الحجم')->required(),
                                TextInput::make('name_en')->label('Option name in English')->placeholder('Size')->required(),
                                Repeater::make('values')
                                    ->label('قيم الخيار')
                                    ->schema([
                                        TextInput::make('code')->label('الرمز')->placeholder('16kg')->required()->alphaDash(),
                                        TextInput::make('label_ar')->label('القيمة بالعربية')->placeholder('16 كيلو')->required(),
                                        TextInput::make('label_en')->label('Value in English')->placeholder('16 KG')->required(),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(1)
                                    ->addActionLabel('إضافة قيمة'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['name_ar'] ?? 'خيار جديد')
                            ->addActionLabel('إضافة خيار للمنتج'),
                    ])
                    ->collapsible(),
                Section::make('متغيرات المنتج / Product Variants')
                    ->description('أنشئ تركيبة لكل مجموعة خيارات، وحدد SKU والسعر والصورة والمخزون المستقل لكل متغير.')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        Repeater::make('variants')
                            ->hiddenLabel()
                            ->relationship()
                            ->schema([
                                TextInput::make('sku')->label('SKU المتغير')->required()->maxLength(100),
                                KeyValue::make('option_values')
                                    ->label('تركيبة الخيارات')
                                    ->keyLabel('الخيار، مثال: size')
                                    ->valueLabel('القيمة، مثال: 16kg')
                                    ->addActionLabel('إضافة خيار'),
                                TextInput::make('price')->label('السعر')->numeric()->prefix('AED'),
                                TextInput::make('compare_at_price')->label('السعر قبل الخصم')->numeric()->prefix('AED'),
                                TextInput::make('quantity')->label('مخزون المتغير')->numeric()->integer()->minValue(0)->default(0)->required(),
                                TextInput::make('sort_order')->label('الترتيب')->numeric()->integer()->default(0),
                                FileUpload::make('image_path')->label('صورة المتغير')->image()->disk('public')->directory('products/variants')->visibility('public')->openable(),
                                Toggle::make('is_active')->label('متغير فعال')->default(true),
                                Repeater::make('translations')
                                    ->label('اسم ووصف المتغير حسب اللغة / Variant translations')
                                    ->relationship()
                                    ->schema([
                                        Select::make('locale')->label('اللغة / Language')->options(['ar' => 'العربية', 'en' => 'English'])->required(),
                                        TextInput::make('name')->label('اسم المتغير / Variant name')->required()->maxLength(255),
                                        Textarea::make('description')->label('وصف المتغير / Variant description')->rows(2)->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): string => ($state['locale'] ?? '—').' — '.($state['name'] ?? 'ترجمة جديدة'))
                                    ->addActionLabel('إضافة لغة للمتغير / Add variant language')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['sku'] ?? 'متغير جديد')
                            ->addActionLabel('إضافة متغير'),
                    ])
                    ->collapsible(),
                Repeater::make('translations')
                    ->label(__('landivo.products.translations'))
                    ->relationship()
                    ->schema([
                        Select::make('locale')->label(__('landivo.products.locale'))->options(__('landivo.locales'))->required(),
                        TextInput::make('name')->label(__('landivo.products.name'))->required(),
                        Textarea::make('description')
                            ->label('وصف المنتج / Product description')
                            ->rows(3)
                            ->columnSpanFull(),
                        RichEditor::make('details')
                            ->label('تفاصيل المنتج / Product details')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
