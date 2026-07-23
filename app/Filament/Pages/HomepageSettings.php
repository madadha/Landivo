<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\ProductStatus;
use Filament\Forms\Components\ColorPicker;
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

    protected static string|\UnitEnum|null $navigationGroup = 'الموقع والمحتوى';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'homepage-settings';

    protected string $view = 'filament.pages.homepage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = array_replace_recursive(
            $this->defaultSettings(),
            (array) (auth()->user()->account?->settings ?? []),
        );

        $this->form->fill(['settings' => $settings]);
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
                    ->description('رتّب الشرائح بالسحب، واربط الشريحة بمنتج أو خصص محتواها وصورها.')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('settings.home_slider_enabled')->label('إظهار السلايدر')->default(true)->live(),
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
                                    ->live(),
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
                                TextInput::make('url')->label('رابط الزر')->placeholder('/products أو رابط خارجي'),
                                Toggle::make('new_tab')->label('فتح في تبويب جديد')->default(false),
                            ])
                            ->columns(2)
                            ->addActionLabel('إضافة شريحة'),
                    ]),

                Section::make('شريط المميزات')
                    ->description('تحكم بالنصوص والأيقونات وترتيب المميزات الظاهرة أسفل السلايدر.')
                    ->schema([
                        Toggle::make('settings.home_features_enabled')->label('إظهار شريط المميزات')->default(true),
                        Repeater::make('settings.home_features')
                            ->label('المميزات')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['title_ar'] ?? $state['title_en'] ?? 'ميزة جديدة')
                            ->schema([
                                Select::make('icon')->label('الأيقونة')->options([
                                    'quality' => 'جودة / نجمة',
                                    'delivery' => 'توصيل',
                                    'support' => 'خدمة ودعم',
                                    'secure' => 'شراء آمن',
                                ])->default('quality')->required(),
                                Toggle::make('is_active')->label('ظاهرة')->default(true),
                                TextInput::make('title_ar')->label('العنوان بالعربية')->required(),
                                TextInput::make('title_en')->label('العنوان بالإنجليزية')->required(),
                                TextInput::make('subtitle_ar')->label('الوصف بالعربية'),
                                TextInput::make('subtitle_en')->label('الوصف بالإنجليزية'),
                            ])
                            ->columns(2)
                            ->addActionLabel('إضافة ميزة'),
                    ])
                    ->collapsible(),

                Section::make('منتجات الصفحة الرئيسية')
                    ->description('ترتيب المنتجات يعتمد على رقم ترتيب العرض داخل كل منتج. الأصغر يظهر أولًا.')
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
                            TextInput::make('settings.home_products_kicker_ar')->label('العنوان الصغير بالعربية'),
                            TextInput::make('settings.home_products_kicker_en')->label('العنوان الصغير بالإنجليزية'),
                            TextInput::make('settings.home_products_title_ar')->label('عنوان القسم بالعربية'),
                            TextInput::make('settings.home_products_title_en')->label('عنوان القسم بالإنجليزية'),
                            Textarea::make('settings.home_products_description_ar')->label('وصف القسم بالعربية')->rows(2),
                            Textarea::make('settings.home_products_description_en')->label('وصف القسم بالإنجليزية')->rows(2),
                            TextInput::make('settings.home_products_view_all_ar')->label('نص عرض الكل بالعربية'),
                            TextInput::make('settings.home_products_view_all_en')->label('نص عرض الكل بالإنجليزية'),
                        ]),
                        Repeater::make('settings.home_products')
                            ->label('تحديد منتجات بعينها (اختياري)')
                            ->helperText('اتركها فارغة لعرض المنتجات الفعالة حسب رقم ترتيب العرض في صفحة المنتج.')
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

                Section::make('صفحة المنتجات')
                    ->description('اختر بين الترقيم التقليدي أو تحميل المنتجات تلقائيًا عند النزول، مع إبقاء زر تحميل المزيد كخيار احتياطي.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('settings.products_load_mode')
                                ->label('طريقة عرض بقية المنتجات')
                                ->options([
                                    'pagination' => 'ترقيم صفحات',
                                    'infinite' => 'تحميل تلقائي عند النزول',
                                ])
                                ->default('pagination')
                                ->required(),
                            TextInput::make('settings.products_per_page')
                                ->label('عدد المنتجات في كل دفعة')
                                ->numeric()
                                ->minValue(4)
                                ->maxValue(48)
                                ->default(12)
                                ->required(),
                            TextInput::make('settings.products_load_more_ar')
                                ->label('نص زر التحميل بالعربية'),
                            TextInput::make('settings.products_load_more_en')
                                ->label('نص زر التحميل بالإنجليزية'),
                        ]),
                    ])
                    ->collapsible(),

                Section::make('قسم التعريف والصورة الجانبية')
                    ->description('تحكم كامل بالقسم التعريفي وصورته ونصوصه وأزراره.')
                    ->schema([
                        Toggle::make('settings.home_about_enabled')->label('إظهار القسم التعريفي')->default(true),
                        FileUpload::make('settings.home_about_image')
                            ->label('الصورة الجانبية')
                            ->image()
                            ->disk('public')
                            ->directory('homepage/about')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('settings.home_about_kicker_ar')->label('العنوان الصغير بالعربية'),
                            TextInput::make('settings.home_about_kicker_en')->label('العنوان الصغير بالإنجليزية'),
                            TextInput::make('settings.home_about_title_ar')->label('العنوان بالعربية'),
                            TextInput::make('settings.home_about_title_en')->label('العنوان بالإنجليزية'),
                            Textarea::make('settings.home_about_description_ar')->label('الوصف بالعربية')->rows(3),
                            Textarea::make('settings.home_about_description_en')->label('الوصف بالإنجليزية')->rows(3),
                            TextInput::make('settings.home_about_button_ar')->label('نص الزر بالعربية'),
                            TextInput::make('settings.home_about_button_en')->label('نص الزر بالإنجليزية'),
                            TextInput::make('settings.home_about_url')->label('رابط الزر')->placeholder('/about-us'),
                            Toggle::make('settings.home_about_new_tab')->label('فتح في تبويب جديد')->default(false),
                        ]),
                    ])
                    ->collapsible(),

                Section::make('قسم عروض صفحات الهبوط')
                    ->description('تحكم بعنوان قسم العروض وعدد البطاقات والنصوص الظاهرة.')
                    ->schema([
                        Grid::make(3)->schema([
                            Toggle::make('settings.home_show_campaigns')->label('إظهار قسم العروض')->default(true),
                            TextInput::make('settings.home_campaigns_limit')->label('أقصى عدد')->numeric()->minValue(1)->maxValue(12)->default(6),
                            Select::make('settings.home_campaigns_columns')->label('عدد الأعمدة')->options([2 => 'عمودان', 3 => '3 أعمدة'])->default(3),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('settings.home_campaigns_kicker_ar')->label('العنوان الصغير بالعربية'),
                            TextInput::make('settings.home_campaigns_kicker_en')->label('العنوان الصغير بالإنجليزية'),
                            TextInput::make('settings.home_campaigns_title_ar')->label('العنوان بالعربية'),
                            TextInput::make('settings.home_campaigns_title_en')->label('العنوان بالإنجليزية'),
                            TextInput::make('settings.home_campaign_card_label_ar')->label('وسم البطاقة بالعربية'),
                            TextInput::make('settings.home_campaign_card_label_en')->label('وسم البطاقة بالإنجليزية'),
                            TextInput::make('settings.home_campaign_button_ar')->label('نص زر العرض بالعربية'),
                            TextInput::make('settings.home_campaign_button_en')->label('نص زر العرض بالإنجليزية'),
                        ]),
                    ])
                    ->collapsible(),

                Section::make('القائمة الرئيسية')
                    ->description('أضف روابط القائمة ورتّبها بالسحب.')
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

                Section::make('الفوتر الكامل')
                    ->description('تحكم بالشعار والمحتوى والروابط والتواصل والألوان والنص السفلي.')
                    ->schema([
                        Grid::make(4)->schema([
                            Toggle::make('settings.footer_enabled')->label('إظهار الفوتر')->default(true),
                            ColorPicker::make('settings.footer_background_color')->label('لون الخلفية'),
                            ColorPicker::make('settings.footer_text_color')->label('لون النص'),
                            ColorPicker::make('settings.footer_accent_color')->label('اللون البارز'),
                        ]),
                        FileUpload::make('settings.footer_logo_path')
                            ->label('شعار خاص بالفوتر (اختياري)')
                            ->image()
                            ->disk('public')
                            ->directory('homepage/footer')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            Textarea::make('settings.footer_description_ar')->label('وصف الشركة بالعربية')->rows(4),
                            Textarea::make('settings.footer_description_en')->label('وصف الشركة بالإنجليزية')->rows(4),
                            TextInput::make('settings.footer_links_title_ar')->label('عنوان الروابط بالعربية'),
                            TextInput::make('settings.footer_links_title_en')->label('عنوان الروابط بالإنجليزية'),
                            TextInput::make('settings.footer_contact_title_ar')->label('عنوان التواصل بالعربية'),
                            TextInput::make('settings.footer_contact_title_en')->label('عنوان التواصل بالإنجليزية'),
                            TextInput::make('settings.footer_help_title_ar')->label('عنوان المساعدة بالعربية'),
                            TextInput::make('settings.footer_help_title_en')->label('عنوان المساعدة بالإنجليزية'),
                            Textarea::make('settings.footer_help_text_ar')->label('نص المساعدة بالعربية')->rows(2),
                            Textarea::make('settings.footer_help_text_en')->label('نص المساعدة بالإنجليزية')->rows(2),
                            TextInput::make('settings.footer_whatsapp_button_ar')->label('نص زر واتساب بالعربية'),
                            TextInput::make('settings.footer_whatsapp_button_en')->label('نص زر واتساب بالإنجليزية'),
                            TextInput::make('settings.footer_copyright_ar')->label('حقوق النشر بالعربية')->helperText('استخدم {year} و {company} ليتم استبدالهما تلقائيًا.'),
                            TextInput::make('settings.footer_copyright_en')->label('حقوق النشر بالإنجليزية')->helperText('Use {year} and {company} as dynamic variables.'),
                            TextInput::make('settings.footer_trust_ar')->label('النص السفلي بالعربية'),
                            TextInput::make('settings.footer_trust_en')->label('النص السفلي بالإنجليزية'),
                        ]),
                        Repeater::make('settings.footer_menu')
                            ->label('روابط الفوتر')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['label_ar'] ?? $state['label_en'] ?? 'رابط جديد')
                            ->schema($this->menuItemSchema())
                            ->columns(2)
                            ->addActionLabel('إضافة رابط للفوتر'),
                        Repeater::make('settings.social_links')
                            ->label('روابط التواصل الاجتماعي')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['label'] ?? $state['platform'] ?? 'منصة جديدة')
                            ->schema([
                                Select::make('platform')->label('المنصة')->options([
                                    'facebook' => 'Facebook',
                                    'instagram' => 'Instagram',
                                    'whatsapp' => 'WhatsApp',
                                    'tiktok' => 'TikTok',
                                    'youtube' => 'YouTube',
                                    'x' => 'X',
                                    'website' => 'Website',
                                ])->required(),
                                TextInput::make('label')->label('الاسم'),
                                TextInput::make('url')->label('الرابط')->url()->required(),
                            ])
                            ->columns(3)
                            ->addActionLabel('إضافة منصة'),
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
        return Product::query()
            ->with('translations')
            ->where('account_id', auth()->user()->account_id)
            ->where('status', ProductStatus::Active->value)
            ->orderBy('sort_order')
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

    private function defaultSettings(): array
    {
        return [
            'home_slider_enabled' => true,
            'home_slider_interval' => 6,
            'home_features_enabled' => true,
            'home_features' => [
                ['icon' => 'quality', 'title_ar' => 'جودة مختارة', 'title_en' => 'Selected quality', 'subtitle_ar' => 'منتجات نفخر بتقديمها', 'subtitle_en' => 'Products we are proud to offer', 'is_active' => true],
                ['icon' => 'delivery', 'title_ar' => 'توصيل سريع', 'title_en' => 'Fast delivery', 'subtitle_ar' => 'حتى باب منزلك', 'subtitle_en' => 'Straight to your doorstep', 'is_active' => true],
                ['icon' => 'support', 'title_ar' => 'خدمة متواصلة', 'title_en' => 'Continuous support', 'subtitle_ar' => 'نحن هنا لمساعدتك', 'subtitle_en' => 'We are here to help', 'is_active' => true],
                ['icon' => 'secure', 'title_ar' => 'شراء آمن', 'title_en' => 'Secure shopping', 'subtitle_ar' => 'دفع وتجربة موثوقة', 'subtitle_en' => 'A trusted checkout experience', 'is_active' => true],
            ],
            'home_show_products' => true,
            'home_products_limit' => 8,
            'home_products_desktop_columns' => 4,
            'home_products_mobile_columns' => 2,
            'home_products_kicker_ar' => 'مختارة لك',
            'home_products_kicker_en' => 'Selected for you',
            'home_products_title_ar' => 'منتجاتنا',
            'home_products_title_en' => 'Our products',
            'home_products_description_ar' => 'تعرّف على أحدث المنتجات والعروض المتوفرة.',
            'home_products_description_en' => 'Explore our latest products and available offers.',
            'home_products_view_all_ar' => 'عرض الكل',
            'home_products_view_all_en' => 'View all',
            'products_load_mode' => 'pagination',
            'products_per_page' => 12,
            'products_load_more_ar' => 'تحميل المزيد',
            'products_load_more_en' => 'Load more',
            'home_about_enabled' => true,
            'home_about_kicker_ar' => 'قصتنا',
            'home_about_kicker_en' => 'Our story',
            'home_about_title_ar' => 'نختار الأفضل لنقدمه لك',
            'home_about_title_en' => 'We select the best for you',
            'home_about_button_ar' => 'اعرف المزيد عنا',
            'home_about_button_en' => 'Learn more about us',
            'home_show_campaigns' => true,
            'home_campaigns_limit' => 6,
            'home_campaigns_columns' => 3,
            'home_campaigns_kicker_ar' => 'عروض مباشرة',
            'home_campaigns_kicker_en' => 'Live offers',
            'home_campaigns_title_ar' => 'اكتشف عروضنا',
            'home_campaigns_title_en' => 'Discover our offers',
            'home_campaign_card_label_ar' => 'عرض متاح',
            'home_campaign_card_label_en' => 'Available offer',
            'home_campaign_button_ar' => 'مشاهدة العرض',
            'home_campaign_button_en' => 'View offer',
            'footer_enabled' => true,
            'footer_background_color' => '#0b1428',
            'footer_text_color' => '#aeb7c7',
            'footer_accent_color' => '#d8e77a',
            'footer_links_title_ar' => 'روابط سريعة',
            'footer_links_title_en' => 'Quick links',
            'footer_contact_title_ar' => 'تواصل معنا',
            'footer_contact_title_en' => 'Contact us',
            'footer_help_title_ar' => 'هل تحتاج مساعدة؟',
            'footer_help_title_en' => 'Need help?',
            'footer_help_text_ar' => 'فريقنا جاهز لمساعدتك واختيار العرض المناسب.',
            'footer_help_text_en' => 'Our team is ready to help you choose the right offer.',
            'footer_whatsapp_button_ar' => 'تواصل عبر واتساب',
            'footer_whatsapp_button_en' => 'Chat on WhatsApp',
            'footer_copyright_ar' => 'جميع الحقوق محفوظة © {year} {company}',
            'footer_copyright_en' => 'All rights reserved © {year} {company}',
            'footer_trust_ar' => 'تجربة رقمية موثوقة',
            'footer_trust_en' => 'A trusted digital experience',
        ];
    }
}
