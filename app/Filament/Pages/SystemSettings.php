<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SystemSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'إعدادات النظام';

    protected static ?string $title = 'إعدادات النظام';

    protected static ?string $slug = 'system-settings';

    protected static bool $shouldRegisterNavigation = true;

    protected static string|\UnitEnum|null $navigationGroup = 'إدارة النظام';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.system-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $account = auth()->user()->account;
        $this->form->fill([
            'name' => $account?->name,
            'slug' => $account?->slug,
            'description' => $account?->description,
            'logo_path' => $account?->logo_path,
            'favicon_path' => $account?->favicon_path,
            'company_details' => $account?->company_details,
            'default_locale' => $account?->default_locale ?? 'ar',
            'phone_country_code' => $account?->phone_country_code ?? '971',
            'settings' => $account?->settings ?? [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('هوية الشركة / Company identity')->schema([
                Grid::make(2)->schema([
                    FileUpload::make('logo_path')->label('شعار الشركة / Company logo')->image()->disk('public')->directory('accounts/logos')->imageEditor(),
                    FileUpload::make('favicon_path')->label('أيقونة الموقع / Favicon')->image()->disk('public')->directory('accounts/favicon')->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/jpeg'])->maxSize(2048)->helperText('ارفع favicon.ico أو صورة PNG مربعة.'),
                ]),
                TextInput::make('name')->label('اسم الشركة')->required(),
                TextInput::make('slug')->label('المعرّف الداخلي')->disabled(),
                Grid::make(2)->schema([
                    Textarea::make('description')->label('وصف الشركة بالعربية')->rows(3),
                    Textarea::make('settings.description_en')->label('Company description in English')->rows(3),
                    Textarea::make('company_details')->label('تفاصيل الشركة بالعربية')->rows(4),
                    Textarea::make('settings.company_details_en')->label('Company details in English')->rows(4),
                ]),
            ]),
            Section::make('إعدادات اللغة والهاتف / Locale settings')->schema([
                Select::make('default_locale')->label('اللغة الافتراضية / Default language')->options(['ar' => 'العربية', 'en' => 'English'])->required(),
                TextInput::make('phone_country_code')->label('كود الدولة للهاتف / Phone country code')->prefix('+')->numeric()->required()->maxLength(6),
            ])->columns(2),
            Section::make('بيانات التحويل البنكي / Bank transfer details')
                ->description('تُستخدم هذه البيانات لإنشاء رسالة واتساب جاهزة وآمنة من صفحة الطلب.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('settings.bank_name')
                            ->label('اسم البنك / Bank Name')
                            ->default('ADIB')
                            ->maxLength(120),
                        TextInput::make('settings.bank_account_holder')
                            ->label('اسم صاحب الحساب / Account Holder Name')
                            ->default('RAAD SATEA SALEM ALMDADHA')
                            ->maxLength(180),
                        TextInput::make('settings.bank_account_number')
                            ->label('رقم الحساب / Account Number')
                            ->default('19546383')
                            ->maxLength(60),
                        TextInput::make('settings.bank_iban')
                            ->label('رقم الآيبان / IBAN')
                            ->default('AE950500000000019546383')
                            ->maxLength(60),
                        TextInput::make('settings.bank_swift')
                            ->label('رمز SWIFT / SWIFT Code')
                            ->default('ABDIAEADXXX')
                            ->maxLength(30),
                        TextInput::make('settings.bank_currency')
                            ->label('العملة / Currency')
                            ->default('AED')
                            ->maxLength(10),
                    ]),
                ])
                ->collapsible(),
            Section::make('أمان لوحة التحكم / Admin security')
                ->description('تحكم بخطوات حماية تسجيل دخول مستخدمي لوحة الإدارة.')
                ->schema([
                    Toggle::make('settings.admin_email_mfa_enabled')
                        ->label('طلب رمز التحقق عند دخول لوحة التحكم')
                        ->helperText('بعد التأكد من البريد وكلمة المرور، سيُرسل رمز من 6 أرقام إلى بريد المستخدم. تنتهي صلاحيته خلال 5 دقائق.')
                        ->default(false)
                        ->inline(false),
                ]),
            Section::make('الصفحة الرئيسية / Homepage')->description('تحكم بمحتوى الصفحة الرئيسية العامة على الرابط /.')->schema([
                Grid::make(2)->schema([
                    TextInput::make('settings.home_title_ar')->label('العنوان بالعربية')->default('صفحات الهبوط'),
                    TextInput::make('settings.home_title_en')->label('Title in English')->default('Landing Pages'),
                    Textarea::make('settings.home_description_ar')->label('الوصف بالعربية')->rows(2),
                    Textarea::make('settings.home_description_en')->label('Description in English')->rows(2),
                ]),
                Repeater::make('settings.home_slides')->label('سلايدر الصفحة الرئيسية / Homepage Slider')->collapsed()->itemLabel(fn (array $state): string => $state['title_ar'] ?? $state['title_en'] ?? 'شريحة جديدة')->schema([
                    FileUpload::make('image')->label('صورة الشريحة')->image()->disk('public')->directory('homepage/slides')->imageEditor()->required()->columnSpanFull(),
                    TextInput::make('title_ar')->label('العنوان بالعربية'),
                    TextInput::make('title_en')->label('English title'),
                    Textarea::make('description_ar')->label('الوصف بالعربية')->rows(2),
                    Textarea::make('description_en')->label('English description')->rows(2),
                    TextInput::make('button_ar')->label('نص الزر بالعربية'),
                    TextInput::make('button_en')->label('English button text'),
                    TextInput::make('url')->label('رابط الزر')->placeholder('/products'),
                    Toggle::make('is_active')->label('فعالة')->default(true),
                ])->columns(2)->addActionLabel('إضافة شريحة'),
                Section::make('قوائم الموقع / Website menus')->description('تحكم الكامل بروابط القائمة الرئيسية والفوتر وترتيبها. استخدم رابطًا داخليًا مثل /products أو رابطًا خارجيًا كاملًا.')->schema([
                    Repeater::make('settings.header_menu')->label('القائمة الرئيسية / Header menu')->collapsed()->reorderable()->itemLabel(fn (array $state): string => $state['label_ar'] ?? $state['label_en'] ?? 'رابط جديد')->schema([
                        TextInput::make('label_ar')->label('الاسم بالعربية')->required(),
                        TextInput::make('label_en')->label('English label')->required(),
                        TextInput::make('url')->label('الرابط / URL')->placeholder('/products')->required(),
                        Toggle::make('is_active')->label('مفعّل')->default(true),
                        Toggle::make('new_tab')->label('فتح في تبويب جديد')->default(false),
                    ])->columns(2)->addActionLabel('إضافة رابط للقائمة الرئيسية'),
                    Repeater::make('settings.footer_menu')->label('قائمة الفوتر / Footer menu')->collapsed()->reorderable()->itemLabel(fn (array $state): string => $state['label_ar'] ?? $state['label_en'] ?? 'رابط جديد')->schema([
                        TextInput::make('label_ar')->label('الاسم بالعربية')->required(),
                        TextInput::make('label_en')->label('English label')->required(),
                        TextInput::make('url')->label('الرابط / URL')->placeholder('/privacy-policy')->required(),
                        Toggle::make('is_active')->label('مفعّل')->default(true),
                        Toggle::make('new_tab')->label('فتح في تبويب جديد')->default(false),
                    ])->columns(2)->addActionLabel('إضافة رابط للفوتر'),
                ])->columns(2)->collapsible(),
                Grid::make(2)->schema([
                    Toggle::make('settings.home_show_products')->label('إظهار قسم المنتجات')->default(true),
                    Toggle::make('settings.home_show_campaigns')->label('إظهار العروض وصفحات الهبوط')->default(true),
                    TextInput::make('settings.contact_email')->label('بريد التواصل')->email(),
                    TextInput::make('settings.contact_phone')->label('هاتف التواصل'),
                    TextInput::make('settings.contact_whatsapp')->label('رقم واتساب')->helperText('مع كود الدولة، مثال: 971501234567'),
                    TextInput::make('settings.contact_address_ar')->label('العنوان بالعربية'),
                    TextInput::make('settings.contact_address_en')->label('Address in English'),
                ]),
                Select::make('settings.social_icon_style')->label('نمط أيقونات التواصل')->options(['circle' => 'دائري', 'soft' => 'ناعم', 'outline' => 'إطار'])->default('circle'),
                Repeater::make('settings.social_links')->label('التواصل الاجتماعي في الصفحة الرئيسية')->schema([
                    Select::make('platform')->label('المنصة')->options(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'whatsapp' => 'WhatsApp', 'x' => 'X'])->required(),
                    TextInput::make('label')->label('الاسم الظاهر')->required(),
                    TextInput::make('url')->label('الرابط')->url()->required(),
                ])->columns(3)->addActionLabel('إضافة رابط تواصل'),
                Grid::make(2)->schema([
                    RichEditor::make('settings.home_footer_ar')->label('فوتر الصفحة الرئيسية بالعربية')->columnSpanFull(),
                    RichEditor::make('settings.home_footer_en')->label('Homepage footer in English')->columnSpanFull(),
                ]),
            ]),
            Section::make('تهيئة محركات البحث / SEO')->description('إعدادات ظهور الصفحة الرئيسية في Google وربط أدوات التحليل والفهرسة.')->schema([
                Grid::make(2)->schema([
                    TextInput::make('settings.seo_title_ar')->label('عنوان الصفحة بالعربية')->maxLength(60),
                    TextInput::make('settings.seo_title_en')->label('Homepage SEO title')->maxLength(60),
                    Textarea::make('settings.seo_description_ar')->label('الوصف التعريفي بالعربية')->rows(3)->maxLength(160),
                    Textarea::make('settings.seo_description_en')->label('Homepage meta description')->rows(3)->maxLength(160),
                    TextInput::make('settings.seo_keywords')->label('الكلمات المفتاحية / Keywords')->helperText('افصل الكلمات بفواصل.'),
                    TextInput::make('settings.seo_canonical_url')->label('الرابط الأساسي / Canonical URL')->url()->placeholder('https://example.com/'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('settings.google_site_verification')->label('Google Search Console verification')->helperText('الصق قيمة content من وسم Google فقط.'),
                    TextInput::make('settings.google_analytics_id')->label('Google Analytics Measurement ID')->placeholder('G-XXXXXXXXXX'),
                    Toggle::make('settings.seo_indexable')->label('السماح بفهرسة الموقع')->default(true),
                    Toggle::make('settings.sitemap_enabled')->label('تفعيل Sitemap تلقائياً')->default(true),
                ]),
                Textarea::make('settings.seo_head_code')->label('أكواد Head إضافية')->rows(6)->helperText('لإضافة Meta tags أو أدوات التحقق الأخرى مثل Bing Webmaster.'),
            ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $account = auth()->user()->account;

        if (! $account) {
            return;
        }

        $account->fill($state);
        $account->settings = $state['settings'] ?? [];
        $account->save();

        Notification::make()->success()->title('تم حفظ إعدادات النظام')->send();
    }
}
