<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\ProductStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HomepageSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?string $navigationLabel = 'إدارة الصفحة الرئيسية';

    protected static ?string $title = 'إدارة الصفحة الرئيسية';

    protected static string|\UnitEnum|null $navigationGroup = 'الموقع';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'homepage-settings';

    protected string $view = 'filament.pages.homepage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'settings' => (array) (auth()->user()->account?->settings ?? []),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('المحتوى الرئيسي')
                    ->description('العنوان والوصف الظاهران في الصفحة الرئيسية باللغتين.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('settings.home_title_ar')->label('العنوان بالعربية'),
                            TextInput::make('settings.home_title_en')->label('العنوان بالإنجليزية'),
                            Textarea::make('settings.home_description_ar')->label('الوصف بالعربية')->rows(2),
                            Textarea::make('settings.home_description_en')->label('الوصف بالإنجليزية')->rows(2),
                        ]),
                    ])
                    ->collapsible(),

                Section::make('سلايدر الصفحة الرئيسية')
                    ->description('رتّب الشرائح بالسحب. يمكن إنشاء شريحة مخصصة أو ربطها بمنتج ليتم جلب صورته ونصه تلقائيًا.')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('settings.home_slider_enabled')
                                ->label('إظهار السلايدر')
                                ->default(true)
                                ->live(),
                            TextInput::make('settings.home_slider_interval')
                                ->label('مدة عرض الشريحة بالثواني')
                                ->numeric()
                                ->minValue(2)
                                ->maxValue(30)
                                ->default(6),
                        ]),
                        Repeater::make('settings.home_slides')
                            ->label('الشرائح')
                            ->visible(fn (Get $get): bool => (bool) $get('settings.home_slider_enabled'))
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): string => $state['title_ar']
                                ?? $state['title_en']
                                ?? $this->productLabel($state['product_id'] ?? null)
                                ?? 'شريحة جديدة')
                            ->schema([
                                Select::make('product_id')
                                    ->label('ربط بمنتج (اختياري)')
                                    ->options(fn (): array => $this->productOptions())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->helperText('عند اختيار منتج تُستخدم صورته وعنوانه ورابطه تلقائيًا، ويمكنك تخصيصها من الحقول التالية.'),
                                Toggle::make('is_active')->label('مفعّلة')->default(true),
                                FileUpload::make('image')
                                    ->label('صورة الحاسوب')
                                    ->image()
                                    ->disk('public')
                                    ->directory('homepage/slides')
                                    ->imageEditor()
                                    ->required(fn (Get $get): bool => blank($get('product_id')))
                                    ->columnSpanFull(),
                                FileUpload::make('mobile_image')
                                    ->label('صورة الموبايل (اختيارية)')
                                    ->helperText('إذا تُركت فارغة ستُستخدم صورة الحاسوب.')
                                    ->image()
                                    ->disk('public')
                                    ->directory('homepage/slides/mobile')
                                    ->imageEditor()
                                    ->columnSpanFull(),
                                TextInput::make('title_ar')->label('العنوان بالعربية'),
                                TextInput::make('title_en')->label('العنوان بالإنجليزية'),
                                Textarea::make('description_ar')->label('الوصف بالعربية')->rows(2),
                                Textarea::make('description_en')->label('الوصف بالإنجليزية')->rows(2),
                                TextInput::make('button_ar')->label('نص الزر بالعربية'),
                                TextInput::make('button_en')->label('نص الزر بالإنجليزية'),
                                TextInput::make('url')
                                    ->label('رابط الزر')
                                    ->placeholder('/products أو رابط خارجي'),
                                Toggle::make('new_tab')->label('فتح الرابط في تبويب جديد')->default(false),
                            ])
                            ->columns(2)
                            ->addActionLabel('إضافة شريحة'),
                    ]),

                Section::make('منتجات الصفحة الرئيسية')
                    ->description('اختر المنتجات الظاهرة ورتّبها بالسحب. عند ترك القائمة فارغة ستظهر أحدث المنتجات الفعالة تلقائيًا.')
                    ->schema([
                        Grid::make(4)->schema([
                            Toggle::make('settings.home_show_products')->label('إظهار قسم المنتجات')->default(true),
                            TextInput::make('settings.home_products_limit')->label('أقصى عدد')->numeric()->minValue(1)->maxValue(24)->default(8),
                            Select::make('settings.home_products_desktop_columns')
                                ->label('أعمدة الحاسوب')
                                ->options([2 => 'عمودان', 3 => '3 أعمدة', 4 => '4 أعمدة'])
                                ->default(4),
                            Select::make('settings.home_products_mobile_columns')
                                ->label('أعمدة الموبايل')
                                ->options([1 => 'منتج واحد', 2 => 'منتجان'])
                                ->default(2),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('settings.home_products_title_ar')->label('عنوان القسم بالعربية')->default('منتجاتنا'),
                            TextInput::make('settings.home_products_title_en')->label('عنوان القسم بالإنجليزية')->default('Our products'),
                            Textarea::make('settings.home_products_description_ar')->label('وصف القسم بالعربية')->rows(2),
                            Textarea::make('settings.home_products_description_en')->label('وصف القسم بالإنجليزية')->rows(2),
                        ]),
                        Repeater::make('settings.home_products')
                            ->label('المنتجات المختارة')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $this->productLabel($state['product_id'] ?? null) ?? 'اختر منتجًا')
                            ->schema([
                                Select::make('product_id')
                                    ->label('المنتج')
                                    ->options(fn (): array => $this->productOptions())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Toggle::make('is_active')->label('ظاهر')->default(true),
                            ])
                            ->columns(2)
                            ->addActionLabel('إضافة منتج'),
                    ]),

                Section::make('القائمة الرئيسية')
                    ->description('أضف الروابط ورتّبها بالسحب. يمكن استخدام رابط داخلي مثل /products أو رابط خارجي كامل.')
                    ->schema([
                        Repeater::make('settings.header_menu')
                            ->label('روابط القائمة')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['label_ar'] ?? $state['label_en'] ?? 'رابط جديد')
                            ->schema($this->menuItemSchema())
                            ->columns(2)
                            ->addActionLabel('إضافة رابط للقائمة'),
                    ]),

                Section::make('قائمة الفوتر')
                    ->description('تحكم بروابط الفوتر وترتيبها بالطريقة نفسها.')
                    ->schema([
                        Repeater::make('settings.footer_menu')
                            ->label('روابط الفوتر')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['label_ar'] ?? $state['label_en'] ?? 'رابط جديد')
                            ->schema($this->menuItemSchema())
                            ->columns(2)
                            ->addActionLabel('إضافة رابط للفوتر'),
                    ])
                    ->collapsible(),
            ]);
    }

    public function save(): void
    {
        $account = auth()->user()->account;

        if (! $account) {
            return;
        }

        $state = $this->form->getState();
        $account->settings = array_replace(
            (array) ($account->settings ?? []),
            (array) ($state['settings'] ?? []),
        );
        $account->save();

        Notification::make()
            ->success()
            ->title('تم حفظ إعدادات الصفحة الرئيسية')
            ->send();
    }

    private function productOptions(): array
    {
        $accountId = auth()->user()->account_id;

        return Product::query()
            ->with('translations')
            ->where('account_id', $accountId)
            ->where('status', ProductStatus::Active->value)
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function (Product $product): array {
                $translation = $product->translations->firstWhere('locale', 'ar')
                    ?? $product->translations->firstWhere('locale', 'en')
                    ?? $product->translations->first();

                return [$product->id => ($translation?->name ?: $product->sku).' — '.$product->sku];
            })
            ->all();
    }

    private function productLabel(mixed $productId): ?string
    {
        if (blank($productId)) {
            return null;
        }

        return $this->productOptions()[(int) $productId] ?? null;
    }

    private function menuItemSchema(): array
    {
        return [
            TextInput::make('label_ar')->label('الاسم بالعربية')->required(),
            TextInput::make('label_en')->label('الاسم بالإنجليزية')->required(),
            TextInput::make('url')->label('الرابط')->placeholder('/products')->required(),
            Toggle::make('is_active')->label('ظاهر')->default(true),
            Toggle::make('new_tab')->label('فتح في تبويب جديد')->default(false),
        ];
    }
}
