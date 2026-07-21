<?php

namespace App\Filament\Resources\LandingPages\Schemas;

use App\Enums\FieldType;
use App\LandingPageStatus;
use App\LandingSectionType;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language as CodeLanguage;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class LandingPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('page_editor')
                    ->tabs([
                        Tab::make(__('landivo.editor.basic'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('قوالب رسائل واتساب للطلبات / Order WhatsApp Templates')
                                    ->description('أنشئ رسالة تلقائية من بيانات العميل والطلب. اكتب المفتاح بين أقواس مثل {customer_name} وسيستبدله النظام بالقيمة الحقيقية عند فتح واتساب من الطلب.')
                                    ->icon('heroicon-o-chat-bubble-left-right')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Textarea::make('settings.order_whatsapp_message_ar')
                                                ->label('رسالة واتساب بالعربية')
                                                ->rows(9)
                                                ->default("مرحبًا {customer_name}،\n\nبخصوص طلبك رقم {order_number}:\nالعرض المختار: {selected_offer}\nالإجمالي: {total} {currency}\n\nيسعدنا خدمتك.")
                                                ->helperText('مثال: مرحبًا {full_name_ar}، العرض الذي اخترته هو {offer}.'),
                                            Textarea::make('settings.order_whatsapp_message_en')
                                                ->label('WhatsApp message in English')
                                                ->rows(9)
                                                ->default("Hello {customer_name},\n\nRegarding your order {order_number}:\nSelected offer: {selected_offer}\nTotal: {total} {currency}\n\nWe are happy to serve you.")
                                                ->helperText('Example: Hello {full_name_en}, your selected offer is {offer}.'),
                                        ]),
                                        Placeholder::make('whatsapp_template_keys')
                                            ->label('المفاتيح المتاحة وكيفية استخدامها')
                                            ->content(function (Get $get): HtmlString {
                                                $systemTokens = [
                                                    'customer_name' => 'اسم العميل المسجل',
                                                    'customer_phone' => 'رقم هاتف العميل',
                                                    'customer_email' => 'بريد العميل',
                                                    'customer_city' => 'مدينة أو إمارة العميل',
                                                    'order_number' => 'رقم الطلب',
                                                    'order_status' => 'حالة الطلب',
                                                    'selected_offer' => 'العرض الذي اختاره العميل',
                                                    'total' => 'إجمالي الطلب',
                                                    'currency' => 'العملة',
                                                    'source' => 'مصدر الطلب أو الحملة',
                                                    'follow_up_at' => 'موعد المتابعة',
                                                    'follow_up_note' => 'ملاحظة المتابعة',
                                                    'landing_page_title' => 'عنوان صفحة الهبوط',
                                                    'landing_page_url' => 'رابط صفحة الهبوط',
                                                    'invoice_url' => 'رابط فاتورة مؤقت وآمن',
                                                    'review_url' => 'رابط تقييم الطلب',
                                                ];
                                                $formTokens = collect($get('settings.order_form_fields') ?? [])
                                                    ->mapWithKeys(function (array $field): array {
                                                        $key = trim((string) ($field['internal_name'] ?? $field['key'] ?? ''));
                                                        $translation = collect($field['translations'] ?? [])->firstWhere('locale', 'ar');
                                                        $label = (string) ($translation['label'] ?? $field['label'] ?? $key);

                                                        return filled($key) ? [$key => 'حقل الفورم: '.$label] : [];
                                                    });
                                                $tokens = collect($systemTokens)->merge($formTokens)->unique();
                                                $items = $tokens->map(function (string $description, string $token): string {
                                                    return '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff">'
                                                        .'<code dir="ltr" style="font-weight:800;color:#0f766e;user-select:all">{'.e($token).'}</code>'
                                                        .'<span style="color:#475569;font-size:13px">'.e($description).'</span></div>';
                                                })->implode('');

                                                return new HtmlString(
                                                    '<div style="padding:14px;border-radius:16px;background:#f8fafc;border:1px solid #e2e8f0">'
                                                    .'<p style="margin:0 0 12px;color:#334155"><strong>طريقة الاستخدام:</strong> انسخ المفتاح وضعه داخل الرسالة في المكان المطلوب. كل حقل جديد تضيفه في Form Builder ويملك <code>internal_name</code> سيظهر هنا تلقائيًا.</p>'
                                                    .'<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:8px">'.$items.'</div>'
                                                    .'<p style="margin:12px 0 0;color:#64748b;font-size:13px">إذا كُتب مفتاح غير صحيح سيبقى ظاهرًا بين الأقواس حتى تستطيع اكتشاف الخطأ وتصحيحه.</p>'
                                                    .'</div>'
                                                );
                                            }),
                                    ])
                                    ->extraAttributes(['class' => 'whatsapp-templates-last'])
                                    ->collapsible(),
                                Section::make(__('landivo.editor.page_identity'))
                                    ->description(__('landivo.editor.page_identity_description'))
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('slug')->label(__('landivo.landing_pages.slug'))->required()->alphaDash()->maxLength(100)->suffixAction(Action::make('open_page')->label('فتح رابط الصفحة')->icon('heroicon-o-arrow-top-right-on-square')->url(fn ($record): ?string => $record?->slug ? url('/l/'.$record->slug) : null)->openUrlInNewTab()),
                                            TextInput::make('template')->label(__('landivo.landing_pages.template'))->required()->default('default'),
                                            Select::make('product_id')->label(__('landivo.landing_pages.product'))->relationship('product', 'sku')->searchable()->preload(),
                                            Select::make('status')->label(__('landivo.landing_pages.status'))->options(collect(LandingPageStatus::cases())->mapWithKeys(fn (LandingPageStatus $status): array => [$status->value => $status->label()])->all())->required()->default(LandingPageStatus::Draft->value),
                                            Select::make('default_locale')->label(__('landivo.landing_pages.default_locale'))->options(__('landivo.locales'))->required()->default('ar'),
                                            TextInput::make('settings.notification_emails')
                                                ->label('بريد تنبيهات الطلبات / Order notification emails')
                                                ->email()
                                                ->helperText('ضع بريداً صحيحاً، وسيصل إليه إشعار كل طلب جديد.'),
                                        ]),
                                    ]),
                                Section::make('مخزون صفحة الهبوط / Landing Page Inventory')
                                    ->description('مخزون مستقل لهذه الحملة. يُخصم تلقائيًا عند نقل الطلب إلى حالة مفعّل فيها خيار خصم المخزون، ويُعاد عند التراجع عنها.')
                                    ->icon('heroicon-o-cube')
                                    ->schema([
                                        Toggle::make('track_inventory')
                                            ->label('تتبع مخزون هذه الصفحة / Track page inventory')
                                            ->default(false)
                                            ->live(),
                                        Grid::make(3)->schema([
                                            TextInput::make('stock_quantity')
                                                ->label('المخزون المتاح / Available stock')
                                                ->numeric()
                                                ->minValue(0)
                                                ->default(0)
                                                ->required(),
                                            TextInput::make('low_stock_threshold')
                                                ->label('حد تنبيه المخزون المنخفض / Low stock alert')
                                                ->numeric()
                                                ->minValue(0)
                                                ->default(5)
                                                ->required(),
                                            Select::make('product_variant_id')
                                                ->label('متغير المنتج لهذه الصفحة / Page product variant')
                                                ->options(function (Get $get): array {
                                                    $productId = (int) $get('product_id');

                                                    if (! $productId) {
                                                        return [];
                                                    }

                                                    return ProductVariant::query()
                                                        ->where('product_id', $productId)
                                                        ->where('is_active', true)
                                                        ->orderBy('sort_order')
                                                        ->get()
                                                        ->mapWithKeys(fn (ProductVariant $variant): array => [$variant->id => $variant->label()])
                                                        ->all();
                                                })
                                                ->searchable()
                                                ->helperText('اختياري: اربط الصفحة بحجم أو عرض محدد من متغيرات المنتج.'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('track_inventory')),
                                    ])
                                    ->collapsible(),
                                Section::make('شارة توثيق العنوان / Title Verification Badge')
                                    ->description('أضف علامة توثيق بجانب عنوان صفحة الهبوط، مع نص وتصميم مستقلين للعربية والإنجليزية.')
                                    ->schema([
                                        Toggle::make('settings.title_badge_enabled')
                                            ->label('تفعيل شارة التوثيق / Enable verification badge')
                                            ->default(false)
                                            ->live(),
                                        Grid::make(2)->schema([
                                            TextInput::make('settings.title_badge_text_ar')->label('النص بالعربية / Arabic text')->placeholder('منتج أصلي'),
                                            TextInput::make('settings.title_badge_text_en')->label('النص بالإنجليزية / English text')->placeholder('Authentic Product'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.title_badge_enabled')),
                                        Grid::make(4)->schema([
                                            Select::make('settings.title_badge_icon')
                                                ->label('شكل علامة التوثيق / Verification mark')
                                                ->options([
                                                    'facebook' => 'دائرة موثقة / Facebook style',
                                                    'instagram' => 'وردة موثقة / Instagram style',
                                                    'whatsapp' => 'شارة واتساب / WhatsApp style',
                                                    'shield' => 'درع موثوق / Trusted shield',
                                                    'seal' => 'ختم أصلي / Authentic seal',
                                                ])->default('facebook'),
                                            Select::make('settings.title_badge_style')
                                                ->label('تصميم الشارة / Badge style')
                                                ->options([
                                                    'icon_only' => 'الشارة فقط بدون نص / Icon only',
                                                    'icon' => 'أيقونة مع نص / Icon with text',
                                                    'pill' => 'كبسولة ملونة / Filled pill',
                                                    'soft' => 'خلفية ناعمة / Soft background',
                                                    'outline' => 'إطار فقط / Outline',
                                                ])->default('icon'),
                                            Select::make('settings.title_badge_placement')
                                                ->label('الموضع / Position')
                                                ->options([
                                                    'start' => 'بداية العنوان تلقائيًا / Auto start',
                                                    'end' => 'نهاية العنوان تلقائيًا / Auto end',
                                                    'above' => 'فوق العنوان بالمنتصف / Above title centered',
                                                ])->default('start'),
                                            Select::make('settings.title_badge_font_family')
                                                ->label('الخط / Font')
                                                ->options([
                                                    'inherit' => 'خط الصفحة / Page font',
                                                    'cairo' => 'Cairo',
                                                    'tajawal' => 'Tajawal',
                                                    'inter' => 'Inter',
                                                    'noto' => 'Noto Sans Arabic',
                                                ])->default('inherit'),
                                            TextInput::make('settings.title_badge_font_size')->label('حجم النص / Font size')->numeric()->minValue(10)->maxValue(30)->default(14),
                                            ColorPicker::make('settings.title_badge_color')->label('لون العلامة / Mark color')->default('#1877F2'),
                                            ColorPicker::make('settings.title_badge_check_color')->label('لون إشارة الصح / Check color')->default('#FFFFFF'),
                                            ColorPicker::make('settings.title_badge_text_color')->label('لون النص / Text color')->default('#172033'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.title_badge_enabled')),
                                    ])
                                    ->collapsible(),
                                Section::make('إشعارات عمليات الشراء / Purchase Notifications')
                                    ->description('اعرض إشعارات شراء أنيقة اعتمادًا على الطلبات الحقيقية، دون إظهار بيانات العميل الحساسة.')
                                    ->schema([
                                        Toggle::make('settings.purchase_notifications_enabled')
                                            ->label('تفعيل إشعارات الشراء / Enable purchase notifications')
                                            ->default(false)
                                            ->live(),
                                        Grid::make(2)->schema([
                                            TextInput::make('settings.purchase_notification_title_ar')->label('النص بالعربية / Arabic text')->placeholder('تم شراء {product} مؤخرًا')->helperText('المتاح: {product} و {count}'),
                                            TextInput::make('settings.purchase_notification_title_en')->label('النص بالإنجليزية / English text')->placeholder('{product} was purchased recently')->helperText('Available: {product} and {count}'),
                                            TextInput::make('settings.purchase_notification_subtitle_ar')->label('الوصف بالعربية / Arabic subtitle')->placeholder('{count_text}')->helperText('استخدم {count_text} لصيغة العدد الصحيحة تلقائيًا.'),
                                            TextInput::make('settings.purchase_notification_subtitle_en')->label('الوصف بالإنجليزية / English subtitle')->placeholder('{count_text}')->helperText('Use {count_text} for automatic singular/plural.'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.purchase_notifications_enabled')),
                                        Grid::make(4)->schema([
                                            Select::make('settings.purchase_notification_scope')->label('مصدر الطلبات / Orders source')->options(['landing_page' => 'هذه الصفحة / This landing page', 'account' => 'جميع صفحات الحساب / All account pages'])->default('landing_page'),
                                            Select::make('settings.purchase_notification_status')->label('حالة الطلب / Order status')->options(['all' => 'جميع الطلبات / All orders', 'completed' => 'الطلبات المكتملة فقط / Completed only'])->default('all'),
                                            Select::make('settings.purchase_notification_position')->label('مكان الظهور / Position')->options(['bottom_start' => 'أسفل البداية / Bottom start', 'bottom_end' => 'أسفل النهاية / Bottom end', 'top_start' => 'أعلى البداية / Top start', 'top_end' => 'أعلى النهاية / Top end'])->default('bottom_start'),
                                            Select::make('settings.purchase_notification_animation')->label('الحركة / Animation')->options(['slide' => 'انزلاق ناعم / Smooth slide', 'pop' => 'تكبير / Pop', 'fade' => 'تلاشي / Fade', 'bounce' => 'ارتداد خفيف / Soft bounce'])->default('slide'),
                                            Select::make('settings.purchase_notification_icon')->label('الأيقونة / Icon')->options(['bag' => 'حقيبة تسوق / Shopping bag', 'cart' => 'سلة شراء / Cart', 'check' => 'عملية مؤكدة / Verified check', 'sparkle' => 'نجمة / Sparkle'])->default('bag'),
                                            TextInput::make('settings.purchase_notification_interval')->label('يظهر كل كم ثانية / Interval seconds')->numeric()->minValue(5)->maxValue(180)->default(12),
                                            TextInput::make('settings.purchase_notification_duration')->label('مدة الظهور بالثواني / Visible duration')->numeric()->minValue(2)->maxValue(20)->default(6),
                                            TextInput::make('settings.purchase_notification_delay')->label('تأخير أول إشعار / Initial delay')->numeric()->minValue(0)->maxValue(120)->default(4),
                                            TextInput::make('settings.purchase_notification_limit')->label('عدد الطلبات المستخدمة / Orders limit')->numeric()->minValue(1)->maxValue(30)->default(10),
                                            Toggle::make('settings.purchase_notification_show_image')->label('إظهار صورة المنتج / Show product image')->default(true),
                                            ColorPicker::make('settings.purchase_notification_accent_color')->label('لون الأيقونة / Accent color')->default('#16A34A'),
                                            ColorPicker::make('settings.purchase_notification_background_color')->label('لون الخلفية / Background')->default('#FFFFFF'),
                                            ColorPicker::make('settings.purchase_notification_text_color')->label('لون النص / Text color')->default('#172033'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.purchase_notifications_enabled')),
                                    ])
                                    ->collapsible(),
                                Section::make('الهوية البصرية / Visual Identity')
                                    ->description('تحكم بخط الصفحة والأحجام واللون الأساسي من المعلومات الأساسية.')
                                    ->schema([
                                        Grid::make(4)->schema([
                                            Select::make('settings.font_family')->label(__('landivo.landing_pages.font_family'))->options(['cairo' => 'Cairo', 'tajawal' => 'Tajawal', 'inter' => 'Inter', 'noto' => 'Noto Sans Arabic'])->default('cairo'),
                                            TextInput::make('settings.heading_size')->label(__('landivo.landing_pages.heading_size'))->numeric()->minValue(24)->maxValue(72)->default(44),
                                            TextInput::make('settings.body_size')->label(__('landivo.landing_pages.body_size'))->numeric()->minValue(14)->maxValue(24)->default(18),
                                            ColorPicker::make('settings.primary_color')->label(__('landivo.landing_pages.primary_color'))->default('#4f46e5'),
                                        ]),
                                    ])
                                    ->collapsible(),
                                Section::make('مظهر عنوان الصفحة / Page Title Appearance')
                                    ->description('خصص عنوان الصفحة ووصفها بالعربية والإنجليزية: الخط والحجم واللون والمحاذاة.')
                                    ->schema([
                                        Grid::make(4)->schema([
                                            Select::make('settings.heading_font_family')->label('خط العنوان / Title font')->options(['inherit' => 'خط الصفحة / Page font', 'cairo' => 'Cairo', 'tajawal' => 'Tajawal', 'inter' => 'Inter', 'noto' => 'Noto Sans Arabic'])->default('inherit'),
                                            ColorPicker::make('settings.heading_color')->label('لون العنوان / Title color')->default('#172033'),
                                            Select::make('settings.heading_alignment_ar')->label('محاذاة العنوان بالعربية / Arabic title alignment')->options(['start' => 'يمين / Right', 'center' => 'وسط / Center', 'end' => 'يسار / Left'])->default('center'),
                                            Select::make('settings.heading_alignment_en')->label('محاذاة العنوان بالإنجليزية / English title alignment')->options(['start' => 'يسار / Left', 'center' => 'وسط / Center', 'end' => 'يمين / Right'])->default('center'),
                                            TextInput::make('settings.description_size')->label('حجم وصف الصفحة / Description size')->numeric()->minValue(12)->maxValue(32)->default(18),
                                            ColorPicker::make('settings.description_color')->label('لون وصف الصفحة / Description color')->default('#667085'),
                                            Select::make('settings.description_alignment_ar')->label('محاذاة الوصف بالعربية / Arabic description alignment')->options(['start' => 'يمين / Right', 'center' => 'وسط / Center', 'end' => 'يسار / Left'])->default('center'),
                                            Select::make('settings.description_alignment_en')->label('محاذاة الوصف بالإنجليزية / English description alignment')->options(['start' => 'يسار / Left', 'center' => 'وسط / Center', 'end' => 'يمين / Right'])->default('center'),
                                        ]),
                                    ])
                                    ->collapsible(),
                                Section::make('الظهور / Visibility')
                                    ->schema([
                                        Toggle::make('settings.show_price')->label(__('landivo.editor.show_price'))->default(true),
                                        Toggle::make('settings.show_compare_price')->label(__('landivo.editor.show_compare_price'))->default(true),
                                        Toggle::make('settings.show_order_form')->label(__('landivo.editor.show_order_form'))->default(true),
                                    ])->columns(3)
                                    ->collapsible(),
                                Section::make('مظهر الأسعار / Price Appearance')
                                    ->description('خصص لون وحجم السعر الحالي والسعر السابق بشكل مستقل.')
                                    ->schema([
                                        Grid::make(4)->schema([
                                            ColorPicker::make('settings.current_price_color')->label(__('landivo.editor.current_price_color'))->default('#4f46e5'),
                                            ColorPicker::make('settings.compare_price_color')->label(__('landivo.editor.compare_price_color'))->default('#98a2b3'),
                                            TextInput::make('settings.current_price_size')->label(__('landivo.editor.current_price_size'))->numeric()->minValue(20)->maxValue(64)->default(32),
                                            TextInput::make('settings.compare_price_size')->label(__('landivo.editor.compare_price_size'))->numeric()->minValue(14)->maxValue(36)->default(20),
                                        ]),
                                    ])
                                    ->collapsible(),
                                Section::make('الرابط وتتبع الحملات / Link & Campaign Tracking')
                                    ->description('استخدم رابطاً مثل: /l/extravirgin2?utm_source=facebook&utm_campaign=summer. ستُحفظ القيم مع الطلب لمعرفة مصدر الإعلان.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('settings.public_link_label_ar')->label('اسم الرابط بالعربية / Arabic link label'),
                                            TextInput::make('settings.public_link_label_en')->label('اسم الرابط بالإنجليزية / English link label'),
                                        ]),
                                        Repeater::make('settings.tracking_parameters')->label('مفاتيح التتبع / Tracking keys')->schema([
                                            TextInput::make('key')->label('المفتاح / Key')->required()->alphaDash()->placeholder('utm_source'),
                                            TextInput::make('label')->label('القيمة أو الوصف / Value label')->placeholder('Facebook'),
                                            TextInput::make('comment')->label('شرح / Comment')->placeholder('مصدر الحملة الإعلانية'),
                                        ])->columns(3)->addActionLabel('إضافة مفتاح / Add key'),
                                    ]),
                                Section::make('رابط صفحة الهبوط / Landing page URL')
                                    ->description('هذا هو الرابط العام الذي ترسله للعملاء أو تستخدمه في الإعلانات.')
                                    ->schema([
                                        TextInput::make('public_preview_url')
                                            ->label('الرابط المباشر / Direct link')
                                            ->formatStateUsing(fn ($record): ?string => $record?->slug ? url('/l/'.$record->slug) : null)
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->suffixAction(Action::make('open_public_page')->label('فتح الرابط')->icon('heroicon-o-arrow-top-right-on-square')->url(fn ($record): ?string => $record?->slug ? url('/l/'.$record->slug) : null)->openUrlInNewTab()),
                                    ]),
                                Section::make(__('landivo.sections.product_gallery'))
                                    ->schema([
                                        Grid::make(2)->schema([
                                            FileUpload::make('settings.product_image_ar')->label(__('landivo.seo.product_image_ar'))->image()->disk('public')->directory('landing-pages/products'),
                                            FileUpload::make('settings.product_image_en')->label(__('landivo.seo.product_image_en'))->image()->disk('public')->directory('landing-pages/products'),
                                        ]),
                                    ]),
                                Section::make(__('landivo.sections.countdown'))
                                    ->schema([
                                        Toggle::make('settings.show_countdown')->label('إظهار العداد / Show countdown')->default(false)->live(),
                                        Grid::make(2)->schema([
                                            DateTimePicker::make('settings.countdown_ends_at')->label('ينتهي العرض في / Offer ends at')->seconds(false)->visible(fn (Get $get): bool => (bool) $get('settings.show_countdown')),
                                            TextInput::make('settings.countdown_title_ar')->label('عنوان العداد بالعربية / Arabic title')->visible(fn (Get $get): bool => (bool) $get('settings.show_countdown')),
                                            TextInput::make('settings.countdown_title_en')->label('عنوان العداد بالإنجليزية / English title')->visible(fn (Get $get): bool => (bool) $get('settings.show_countdown')),
                                            ColorPicker::make('settings.countdown_color')->label('لون أرقام العداد / Number color')->default('#8a9b22')->visible(fn (Get $get): bool => (bool) $get('settings.show_countdown')),
                                            ColorPicker::make('settings.countdown_label_color')->label('لون عناوين العداد / Label color')->default('#667085')->visible(fn (Get $get): bool => (bool) $get('settings.show_countdown')),
                                        ]),
                                    ]),
                                Section::make('دعم واتساب / WhatsApp Support')
                                    ->schema([
                                        Toggle::make('settings.show_whatsapp_support')->label('تفعيل زر واتساب العائم / Enable floating WhatsApp')->default(false)->live(),
                                        Grid::make(2)->schema([
                                            TextInput::make('settings.whatsapp_support_number')->label('رقم واتساب / WhatsApp number')->tel()->visible(fn (Get $get): bool => (bool) $get('settings.show_whatsapp_support')),
                                            TextInput::make('settings.whatsapp_support_message')->label('رسالة البداية / Initial message')->placeholder('مرحباً، أريد الاستفسار عن المنتج')->visible(fn (Get $get): bool => (bool) $get('settings.show_whatsapp_support')),
                                        ]),
                                    ]),
                                Section::make('آراء العملاء والتقييم / Customer Reviews')
                                    ->description('فعّل زر التقييم العائم واعرض التقييمات المعتمدة واسمح للعميل بإرسال رأيه.')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Toggle::make('settings.reviews_enabled')->label('تفعيل التقييم / Enable reviews')->default(false)->live(),
                                            Toggle::make('settings.reviews_allow_submission')->label('السماح بإضافة تقييم / Allow submissions')->default(true)->visible(fn (Get $get): bool => (bool) $get('settings.reviews_enabled')),
                                            Toggle::make('settings.reviews_show_count')->label('إظهار العدد والمتوسط / Show summary')->default(true)->visible(fn (Get $get): bool => (bool) $get('settings.reviews_enabled')),
                                        ]),
                                        Grid::make(3)->schema([
                                            TextInput::make('settings.reviews_button_label_ar')->label('نص الزر بالعربية')->placeholder('آراء العملاء'),
                                            TextInput::make('settings.reviews_button_label_en')->label('Button label in English')->placeholder('Customer reviews'),
                                            Select::make('settings.reviews_button_style')->label('شكل الزر / Button style')->options([
                                                'pill' => 'زر مع نص / Labeled pill',
                                                'icon' => 'أيقونة فقط / Icon only',
                                                'glass' => 'زجاجي / Glass',
                                            ])->default('pill'),
                                            Select::make('settings.reviews_button_position')->label('الموقع / Position')->options([
                                                'bottom_start' => 'أسفل البداية / Bottom start',
                                                'bottom_end' => 'أسفل النهاية / Bottom end',
                                            ])->default('bottom_start'),
                                            ColorPicker::make('settings.reviews_button_color')->label('لون الزر / Button color')->default('#F59E0B'),
                                            ColorPicker::make('settings.reviews_button_text_color')->label('لون النص / Text color')->default('#FFFFFF'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.reviews_enabled')),
                                        Grid::make(2)->schema([
                                            TextInput::make('settings.reviews_trust_text_ar')->label('نص الثقة تحت الشعار')->placeholder('المواسم منذ 10 سنوات — أكثر من 13 ألف عميل استخدموا منتجاتنا'),
                                            TextInput::make('settings.reviews_trust_text_en')->label('Trust text under logo')->placeholder('Al Mawasem for 10 years — trusted by more than 13,000 customers'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.reviews_enabled')),
                                    ])->collapsible(),
                                Section::make('عداد الكمية المحدودة / Limited Stock Counter')
                                    ->description('اعرض الندرة بشكل مهني من كمية يدوية أو من مخزون المنتج المرتبط.')
                                    ->schema([
                                        Toggle::make('settings.limited_stock_enabled')->label('تفعيل عداد الكمية / Enable stock counter')->default(false)->live(),
                                        Grid::make(4)->schema([
                                            Select::make('settings.limited_stock_source')->label('مصدر الكمية / Source')->options(['manual' => 'إدخال يدوي / Manual', 'product' => 'مخزون المنتج / Product stock'])->default('manual'),
                                            TextInput::make('settings.limited_stock_quantity')->label('الكمية المتبقية / Remaining')->numeric()->minValue(0)->default(12),
                                            TextInput::make('settings.limited_stock_total')->label('الكمية الأصلية / Initial total')->numeric()->minValue(1)->default(50),
                                            Select::make('settings.limited_stock_style')->label('التصميم / Style')->options(['bar' => 'شريط تقدم / Progress bar', 'card' => 'بطاقة / Card', 'compact' => 'مختصر / Compact', 'urgent' => 'تنبيه متحرك / Urgent'])->default('bar'),
                                            TextInput::make('settings.limited_stock_label_ar')->label('النص بالعربية')->placeholder('بقي {count} فقط من هذا العرض'),
                                            TextInput::make('settings.limited_stock_label_en')->label('English label')->placeholder('Only {count} left in stock'),
                                            ColorPicker::make('settings.limited_stock_color')->label('لون العداد / Accent')->default('#dc2626'),
                                            ColorPicker::make('settings.limited_stock_background')->label('لون الخلفية / Background')->default('#fff7ed'),
                                            Toggle::make('settings.limited_stock_pulse')->label('حركة تنبيه / Pulse')->default(true),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.limited_stock_enabled')),
                                    ])->collapsible(),
                                Section::make('المتصفحون الآن / Live Viewers')
                                    ->description('عداد حقيقي يحسب الجلسات النشطة لهذه الصفحة دون عرض أي بيانات شخصية.')
                                    ->schema([
                                        Toggle::make('settings.viewer_counter_enabled')->label('تفعيل عداد المتصفحين / Enable live viewers')->default(false)->live(),
                                        Grid::make(4)->schema([
                                            TextInput::make('settings.viewer_counter_label_ar')->label('النص بالعربية')->placeholder('{count} زائر يتصفح هذا العرض الآن'),
                                            TextInput::make('settings.viewer_counter_label_en')->label('English label')->placeholder('{count} people are viewing this offer'),
                                            Select::make('settings.viewer_counter_style')->label('التصميم / Style')->options(['pill' => 'كبسولة / Pill', 'card' => 'بطاقة / Card', 'minimal' => 'مختصر / Minimal', 'glass' => 'زجاجي / Glass'])->default('pill'),
                                            Select::make('settings.viewer_counter_icon')->label('الأيقونة / Icon')->options(['eye' => 'عين / Eye', 'users' => 'زوار / Users', 'pulse' => 'نبض مباشر / Live pulse'])->default('pulse'),
                                            TextInput::make('settings.viewer_counter_window')->label('نافذة النشاط بالدقائق / Active window')->numeric()->minValue(1)->maxValue(60)->default(5),
                                            TextInput::make('settings.viewer_counter_poll')->label('التحديث بالثواني / Refresh')->numeric()->minValue(10)->maxValue(120)->default(30),
                                            ColorPicker::make('settings.viewer_counter_color')->label('لون التمييز / Accent')->default('#16a34a'),
                                            ColorPicker::make('settings.viewer_counter_background')->label('لون الخلفية / Background')->default('#ffffff'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.viewer_counter_enabled')),
                                    ])->collapsible(),
                                Section::make('مظهر تنبيهات الكمية والمتصفحين / Counter Notifications')
                                    ->description('اجمع عداد الكمية والمتصفحين في شرائط مرتبة فوق بعضها دون تداخل، مع تحكم كامل بالموقع والتصميم.')
                                    ->schema([
                                        Grid::make(4)->schema([
                                            Select::make('settings.counter_display_mode')->label('طريقة الظهور / Display')->options(['floating' => 'إشعارات عائمة / Floating bars', 'inline' => 'داخل ترتيب الصفحة / Inline sections'])->default('floating'),
                                            Select::make('settings.counter_notification_position')->label('الموقع / Position')->options(['top_start' => 'أعلى البداية / Top start', 'top_center' => 'أعلى الوسط / Top center', 'top_end' => 'أعلى النهاية / Top end', 'bottom_start' => 'أسفل البداية / Bottom start', 'bottom_center' => 'أسفل الوسط / Bottom center', 'bottom_end' => 'أسفل النهاية / Bottom end'])->default('top_end'),
                                            Select::make('settings.counter_notification_style')->label('التصميم / Style')->options(['clean' => 'نظيف / Clean', 'soft' => 'ناعم / Soft', 'glass' => 'زجاجي / Glass', 'dark' => 'داكن فاخر / Luxury dark', 'brand' => 'لون الهوية / Brand'])->default('soft'),
                                            Select::make('settings.counter_notification_animation')->label('الحركة / Animation')->options(['slide' => 'انزلاق / Slide', 'pop' => 'تكبير / Pop', 'fade' => 'تلاشي / Fade'])->default('slide'),
                                            TextInput::make('settings.counter_notification_width')->label('عرض الشريط / Width')->numeric()->minValue(240)->maxValue(520)->default(370),
                                            TextInput::make('settings.counter_notification_gap')->label('المسافة بين الشرائط / Gap')->numeric()->minValue(6)->maxValue(32)->default(10),
                                            Toggle::make('settings.counter_notification_shadow')->label('ظل احترافي / Shadow')->default(true),
                                            Toggle::make('settings.counter_notification_close')->label('زر إخفاء / Close button')->default(false),
                                        ]),
                                    ])->collapsible(),
                                Section::make('SEO & Tracking')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('settings.seo_title_ar')->label(__('landivo.seo.title_ar')),
                                            TextInput::make('settings.seo_title_en')->label(__('landivo.seo.title_en')),
                                            Textarea::make('settings.seo_description_ar')->label(__('landivo.seo.description_ar'))->rows(3),
                                            Textarea::make('settings.seo_description_en')->label(__('landivo.seo.description_en'))->rows(3),
                                            TextInput::make('settings.seo_keywords')->label(__('landivo.seo.keywords')),
                                            TextInput::make('settings.canonical_url')->label(__('landivo.seo.canonical'))->url(),
                                            Toggle::make('settings.seo_noindex')->label(__('landivo.seo.noindex')),
                                        ]),
                                        FileUpload::make('settings.seo_og_image')->label('Open Graph Image')->image()->disk('public')->directory('landing-pages/seo'),
                                        Grid::make(2)->schema([
                                            Textarea::make('settings.head_code')->label(__('landivo.seo.head_code'))->rows(8),
                                            Textarea::make('settings.body_code')->label(__('landivo.seo.body_code'))->rows(8),
                                        ]),
                                    ]),
                                Section::make(__('landivo.editor.translations'))
                                    ->schema([
                                        Repeater::make('translations')
                                            ->hiddenLabel()
                                            ->relationship()
                                            ->schema([
                                                Select::make('locale')->label(__('landivo.landing_pages.locale'))->options(__('landivo.locales'))->required(),
                                                TextInput::make('title')->label(__('landivo.landing_pages.title'))->required(),
                                                Textarea::make('description')->label(__('landivo.landing_pages.description'))->rows(3)->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel(__('landivo.editor.add_translation'))
                                            ->itemLabel(fn (array $state): ?string => $state['locale'] ?? null),
                                    ]),
                            ]),
                        Tab::make('بطاقات المنتجات / Product Cards')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Section::make('قسم المنتجات المتعدد / Multi-product showcase')
                                    ->description('اختر عدة منتجات وحدد نمط العرض والموضع من قسم ترتيب الصفحة.')
                                    ->schema([
                                        Toggle::make('settings.product_showcase.enabled')->label('تفعيل القسم / Enable section')->default(false),
                                        Grid::make(2)->schema([
                                            Select::make('settings.product_showcase.product_ids')->label('المنتجات / Products')->multiple()->searchable()->preload()->options(fn (): array => Product::query()->orderBy('sku')->pluck('sku', 'id')->all()),
                                            Select::make('settings.product_showcase.layout')->label('طريقة العرض / Layout')->options(['grid' => 'شبكة كروت / Card grid', 'slider' => 'سلايدر / Slider'])->default('grid'),
                                            Select::make('settings.product_showcase.card_style')->label('نمط الكرت / Card style')->options(['classic' => 'كلاسيكي / Classic', 'minimal' => 'بسيط / Minimal', 'glass' => 'زجاجي / Glass', 'featured' => 'مميز / Featured'])->default('classic'),
                                            Toggle::make('settings.product_showcase.autoplay')->label('تشغيل السلايدر تلقائياً / Autoplay')->default(true),
                                            Toggle::make('settings.product_showcase.show_price')->label('إظهار السعر / Show price')->default(true),
                                            Toggle::make('settings.product_showcase.show_discount')->label('إظهار الخصم / Show discount')->default(true),
                                            TextInput::make('settings.product_showcase.title_ar')->label('العنوان بالعربية / Arabic title'),
                                            TextInput::make('settings.product_showcase.title_en')->label('العنوان بالإنجليزية / English title'),
                                            Select::make('settings.product_showcase.title_alignment_ar')
                                                ->label('محاذاة العنوان بالعربية / Arabic alignment')
                                                ->options(['start' => 'يمين / Right', 'center' => 'وسط / Center', 'end' => 'يسار / Left'])
                                                ->default('start'),
                                            Select::make('settings.product_showcase.title_alignment_en')
                                                ->label('محاذاة العنوان بالإنجليزية / English alignment')
                                                ->options(['start' => 'يسار / Left', 'center' => 'وسط / Center', 'end' => 'يمين / Right'])
                                                ->default('start'),
                                            ColorPicker::make('settings.product_showcase.title_color')
                                                ->label('لون العنوان / Title color')
                                                ->default('#172033'),
                                            Select::make('settings.product_showcase.title_font_family')
                                                ->label('خط العنوان / Title font')
                                                ->options([
                                                    'cairo' => 'Cairo',
                                                    'tajawal' => 'Tajawal',
                                                    'noto' => 'Noto Sans Arabic',
                                                    'inter' => 'Inter',
                                                    'system' => 'System UI',
                                                ])
                                                ->default('cairo'),
                                            TextInput::make('settings.product_showcase.title_font_size')
                                                ->label('حجم العنوان بالبكسل / Title size (px)')
                                                ->numeric()
                                                ->minValue(18)
                                                ->maxValue(72)
                                                ->default(32),
                                        ]),
                                    ]),
                            ]),
                        Tab::make(__('landivo.sections.order_form'))
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Section::make(__('landivo.editor.form_fields'))
                                    ->description(__('landivo.editor.options_help'))
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Toggle::make('settings.show_order_form')->label(__('landivo.editor.show_order_form'))->default(true),
                                            TextInput::make('settings.order_form_title_ar')->label('Form title (Arabic)')->placeholder(__('landivo.editor.optional')),
                                            TextInput::make('settings.order_form_title_en')->label('Form title (English)')->placeholder(__('landivo.editor.optional')),
                                            Select::make('settings.order_form_title_alignment_ar')->label('محاذاة العنوان بالعربية / Arabic title alignment')->options(['start' => 'يمين / Right', 'center' => 'وسط / Center', 'end' => 'يسار / Left'])->default('start'),
                                            Select::make('settings.order_form_title_alignment_en')->label('محاذاة العنوان بالإنجليزية / English title alignment')->options(['start' => 'يسار / Left', 'center' => 'وسط / Center', 'end' => 'يمين / Right'])->default('start'),
                                            Select::make('settings.order_form_label_alignment_ar')->label('محاذاة الحقول بالعربية / Arabic labels')->options(['start' => 'يمين / Right', 'center' => 'وسط / Center', 'end' => 'يسار / Left'])->default('start'),
                                            Select::make('settings.order_form_label_alignment_en')->label('محاذاة الحقول بالإنجليزية / English labels')->options(['start' => 'يسار / Left', 'center' => 'Center / وسط', 'end' => 'يمين / Right'])->default('start'),
                                        ]),
                                        Repeater::make('settings.order_form_fields')
                                            ->hiddenLabel()
                                            ->schema([
                                                Select::make('type')->label(__('landivo.editor.field_type'))->options(collect(FieldType::cases())->mapWithKeys(fn (FieldType $type): array => [$type->value => $type->label()])->all())->required()->live(),
                                                Repeater::make('translations')->label(__('landivo.editor.translations'))->schema([
                                                    Select::make('locale')->options(__('landivo.locales'))->required(),
                                                    TextInput::make('label')->label(__('landivo.editor.field_label'))->required(),
                                                    TextInput::make('placeholder')->label(__('landivo.editor.placeholder')),
                                                ])->columns(3)->addActionLabel(__('landivo.editor.add_translation'))->columnSpanFull(),
                                                TextInput::make('internal_name')->label(__('landivo.editor.field_key'))->required()->alphaDash(),
                                                TextInput::make('default_value')->label(__('landivo.field_builder.default_value')),
                                                TextInput::make('sort_order')->label(__('landivo.editor.order'))->numeric()->default(0),
                                                Toggle::make('required')->label(__('landivo.editor.required'))->default(false),
                                                Toggle::make('is_active')->label(__('landivo.editor.visible'))->default(true),
                                                Toggle::make('include_in_invoice')->label(__('landivo.editor.invoice'))->default(true),
                                                Textarea::make('validation_rules')->label(__('landivo.field_builder.validation_rules'))->placeholder('min:1|max:255')->rows(2),
                                                Textarea::make('options')->label(__('landivo.editor.options'))->helperText(__('landivo.editor.options_help'))->rows(3)->visible(fn (Get $get): bool => in_array($get('type'), ['select', 'radio', 'checkbox', 'product_variant', 'country'], true))->columnSpanFull(),
                                                Textarea::make('options_en')->label('Options (English)')->helperText('One option per line. Used on the English page.')->rows(3)->visible(fn (Get $get): bool => in_array($get('type'), ['select', 'radio', 'checkbox', 'product_variant', 'country'], true))->columnSpanFull(),
                                                Repeater::make('option_badges')
                                                    ->label('شارات تمييز الخيارات / Option Badges')
                                                    ->helperText('حدد رقم الخيار حسب ترتيبه، ثم أضف شارة مثل: مميز، الأكثر طلباً، أو وفّر 20%.')
                                                    ->visible(fn (Get $get): bool => in_array($get('type'), ['radio', 'checkbox'], true))
                                                    ->reorderable(false)
                                                    ->collapsible()
                                                    ->itemLabel(fn (array $state): ?string => ($state['badge_text_ar'] ?? $state['badge_text_en'] ?? null) ? 'الخيار '.($state['option_number'] ?? 1).' — '.($state['badge_text_ar'] ?? $state['badge_text_en']) : null)
                                                    ->schema([
                                                        TextInput::make('option_number')->label('رقم الخيار / Option number')->numeric()->minValue(1)->required()->default(1),
                                                        TextInput::make('badge_text_ar')->label('نص الشارة بالعربية')->placeholder('الأكثر طلباً'),
                                                        TextInput::make('badge_text_en')->label('English badge text')->placeholder('Most Popular'),
                                                        Select::make('icon')->label('الأيقونة / Icon')->options(['star' => 'نجمة / Star', 'fire' => 'الأكثر طلباً / Fire', 'percent' => 'خصم / Discount', 'crown' => 'تاج / Crown', 'sparkles' => 'مميز / Sparkles', 'check' => 'موصى به / Recommended'])->default('star'),
                                                        Select::make('style')->label('شكل الشارة / Badge style')->options(['pill' => 'كبسولة / Pill', 'ribbon' => 'شريط زاوية / Ribbon', 'floating' => 'عائمة / Floating', 'outline' => 'إطار / Outline'])->default('pill'),
                                                        ColorPicker::make('background_color')->label('لون الشارة / Background')->default('#F59E0B'),
                                                        ColorPicker::make('text_color')->label('لون النص / Text')->default('#FFFFFF'),
                                                        ColorPicker::make('border_color')->label('لون حدود العرض / Card border')->default('#F59E0B'),
                                                        Toggle::make('highlight_option')->label('تمييز بطاقة العرض / Highlight card')->default(true),
                                                        Toggle::make('pulse')->label('حركة جذب خفيفة / Soft pulse')->default(false),
                                                    ])->columns(5)->addActionLabel('إضافة شارة لخيار / Add option badge')->columnSpanFull(),
                                                Repeater::make('conditions')->label(__('landivo.field_builder.conditions'))->schema([
                                                    TextInput::make('field')->label(__('landivo.field_builder.condition_field'))->required(),
                                                    Select::make('operator')->label(__('landivo.field_builder.condition_operator'))->options(['equals' => '=', 'not_equals' => '≠', 'contains' => 'contains'])->required(),
                                                    TextInput::make('value')->label(__('landivo.field_builder.condition_value'))->required(),
                                                ])->columns(3)->addActionLabel(__('landivo.field_builder.add_condition'))->columnSpanFull(),
                                            ])
                                            ->columns(3)
                                            ->addActionLabel(__('landivo.editor.add_field'))
                                            ->collapsible()
                                            ->collapsed()
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                                    ]),
                                Section::make('تصميم النموذج الاحترافي / Advanced Form Design')
                                    ->description('تحكم كامل بمظهر النموذج والحقول والخيارات وزر الإرسال في العربية والإنجليزية وعلى جميع الأجهزة.')
                                    ->collapsible()
                                    ->schema([
                                        Grid::make(4)->schema([
                                            Select::make('settings.form_style.preset')->label('النمط الجاهز / Preset')->options([
                                                'modern' => 'حديث وناعم / Modern',
                                                'classic' => 'كلاسيكي / Classic',
                                                'minimal' => 'بسيط / Minimal',
                                                'glass' => 'زجاجي / Glass',
                                                'dark' => 'داكن / Dark',
                                                'brand' => 'لون الهوية / Brand',
                                            ])->default('modern'),
                                            Select::make('settings.form_style.font_family')->label('خط النموذج / Form font')->options([
                                                'inherit' => 'خط الصفحة / Inherit',
                                                'cairo' => 'Cairo',
                                                'tajawal' => 'Tajawal',
                                                'noto' => 'Noto Sans Arabic',
                                                'inter' => 'Inter',
                                                'system' => 'System UI',
                                            ])->default('inherit'),
                                            TextInput::make('settings.form_style.max_width')->label('أقصى عرض px / Max width')->numeric()->minValue(320)->maxValue(1200)->default(920),
                                            Select::make('settings.form_style.density')->label('كثافة المسافات / Density')->options(['compact' => 'مدمج / Compact', 'comfortable' => 'مريح / Comfortable', 'spacious' => 'واسع / Spacious'])->default('comfortable'),
                                        ]),
                                        Grid::make(4)->schema([
                                            ColorPicker::make('settings.form_style.card_background')->label('خلفية النموذج / Card background')->default('#FFFFFF'),
                                            ColorPicker::make('settings.form_style.card_border_color')->label('حدود النموذج / Card border')->default('#E2E8F0'),
                                            TextInput::make('settings.form_style.card_radius')->label('استدارة النموذج / Card radius')->numeric()->minValue(0)->maxValue(60)->default(24),
                                            Select::make('settings.form_style.shadow')->label('ظل النموذج / Card shadow')->options(['none' => 'بدون / None', 'soft' => 'ناعم / Soft', 'medium' => 'متوسط / Medium', 'strong' => 'قوي / Strong'])->default('soft'),
                                            ColorPicker::make('settings.form_style.title_color')->label('لون العنوان / Title color')->default('#172033'),
                                            TextInput::make('settings.form_style.title_size')->label('حجم العنوان px / Title size')->numeric()->minValue(18)->maxValue(64)->default(34),
                                            ColorPicker::make('settings.form_style.label_color')->label('لون أسماء الحقول / Label color')->default('#172033'),
                                            TextInput::make('settings.form_style.label_size')->label('حجم أسماء الحقول px / Label size')->numeric()->minValue(11)->maxValue(28)->default(16),
                                        ]),
                                        Section::make('الحقول / Inputs')->compact()->schema([
                                            Grid::make(4)->schema([
                                                ColorPicker::make('settings.form_style.input_background')->label('خلفية الحقل / Background')->default('#FBFCFE'),
                                                ColorPicker::make('settings.form_style.input_text_color')->label('لون النص / Text')->default('#172033'),
                                                ColorPicker::make('settings.form_style.placeholder_color')->label('لون النص الإرشادي / Placeholder')->default('#667085'),
                                                ColorPicker::make('settings.form_style.input_border_color')->label('لون الحدود / Border')->default('#D9E0EA'),
                                                ColorPicker::make('settings.form_style.input_focus_color')->label('لون التركيز / Focus')->default('#8A9B22'),
                                                TextInput::make('settings.form_style.input_radius')->label('استدارة الحقل / Radius')->numeric()->minValue(0)->maxValue(40)->default(16),
                                                TextInput::make('settings.form_style.input_height')->label('ارتفاع الحقل px / Height')->numeric()->minValue(42)->maxValue(90)->default(56),
                                                TextInput::make('settings.form_style.input_font_size')->label('حجم النص px / Font size')->numeric()->minValue(12)->maxValue(26)->default(16),
                                            ]),
                                        ]),
                                        Section::make('خيارات العرض والراديو / Choice Cards')->compact()->schema([
                                            Grid::make(4)->schema([
                                                Select::make('settings.form_style.option_layout')->label('ترتيب الخيارات / Layout')->options(['stack' => 'تحت بعضها / Stack', 'grid_2' => 'عمودان / 2 columns', 'grid_3' => '3 أعمدة / 3 columns'])->default('stack'),
                                                ColorPicker::make('settings.form_style.option_background')->label('خلفية الخيار / Background')->default('#FFFFFF'),
                                                ColorPicker::make('settings.form_style.option_text_color')->label('لون نص الخيار / Text')->default('#172033'),
                                                ColorPicker::make('settings.form_style.option_border_color')->label('لون حدود الخيار / Border')->default('#E2E8F0'),
                                                ColorPicker::make('settings.form_style.option_selected_background')->label('خلفية المحدد / Selected background')->default('#F7F9EA'),
                                                ColorPicker::make('settings.form_style.option_selected_border')->label('حدود المحدد / Selected border')->default('#8A9B22'),
                                                ColorPicker::make('settings.form_style.radio_color')->label('لون دائرة الاختيار / Radio color')->default('#8A9B22'),
                                                TextInput::make('settings.form_style.option_radius')->label('استدارة البطاقة / Radius')->numeric()->minValue(0)->maxValue(40)->default(16),
                                            ]),
                                        ]),
                                        Section::make('زر الإرسال / Submit Button')->compact()->schema([
                                            Grid::make(4)->schema([
                                                TextInput::make('settings.form_style.submit_text_ar')->label('نص الزر بالعربية')->default('إرسال الطلب'),
                                                TextInput::make('settings.form_style.submit_text_en')->label('English button text')->default('Submit Order'),
                                                ColorPicker::make('settings.form_style.button_background')->label('لون الزر / Background')->default('#C91419'),
                                                ColorPicker::make('settings.form_style.button_hover')->label('لون المرور / Hover')->default('#9F1115'),
                                                ColorPicker::make('settings.form_style.button_text_color')->label('لون نص الزر / Text')->default('#FFFFFF'),
                                                TextInput::make('settings.form_style.button_radius')->label('استدارة الزر / Radius')->numeric()->minValue(0)->maxValue(40)->default(16),
                                                TextInput::make('settings.form_style.button_height')->label('ارتفاع الزر px / Height')->numeric()->minValue(44)->maxValue(90)->default(58),
                                                TextInput::make('settings.form_style.button_font_size')->label('حجم نص الزر px / Font size')->numeric()->minValue(13)->maxValue(28)->default(17),
                                            ]),
                                        ]),
                                    ]),
                            ]),
                        Tab::make(__('landivo.sections.gallery'))
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make(__('landivo.sections.gallery'))
                                    ->schema([
                                        FileUpload::make('settings.gallery_images_ar')->label('Gallery images (Arabic)')->image()->multiple()->disk('public')->directory('landing-pages/gallery/ar')->reorderable(),
                                        FileUpload::make('settings.gallery_images_en')->label('Gallery images (English)')->image()->multiple()->disk('public')->directory('landing-pages/gallery/en')->reorderable(),
                                    ]),
                            ]),
                        Tab::make(__('landivo.builder.slider'))
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('سلايدر الصور / Image Slider')
                                    ->description('سلايدر متعدد اللغات ومتجاوب مع عدة أنماط وتحكم كامل بالحركة والصور.')
                                    ->schema([
                                        Toggle::make('settings.slider_enabled')->label('تفعيل السلايدر / Enable slider')->default(true)->live(),
                                        Grid::make(2)->schema([
                                            FileUpload::make('settings.slider_images_ar')->label('صور السلايدر بالعربية / Arabic slides')->image()->multiple()->disk('public')->directory('landing-pages/sliders/ar')->reorderable()->openable(),
                                            FileUpload::make('settings.slider_images_en')->label('صور السلايدر بالإنجليزية / English slides')->image()->multiple()->disk('public')->directory('landing-pages/sliders/en')->reorderable()->openable(),
                                        ])->visible(fn (Get $get): bool => $get('settings.slider_enabled') !== false),
                                        Grid::make(4)->schema([
                                            Select::make('settings.slider_style')->label('نمط العرض / Style')->options(['classic' => 'كلاسيكي / Classic', 'cards' => 'بطاقات / Cards', 'full_bleed' => 'صورة كاملة / Full bleed', 'fade' => 'تلاشي / Fade'])->default('classic'),
                                            Select::make('settings.slider_object_fit')->label('ملاءمة الصورة / Image fit')->options(['cover' => 'تغطية / Cover', 'contain' => 'كاملة / Contain'])->default('cover'),
                                            TextInput::make('settings.slider_height')->label('الارتفاع للحاسوب / Desktop height')->numeric()->minValue(220)->maxValue(900)->default(420),
                                            TextInput::make('settings.slider_mobile_height')->label('الارتفاع للموبايل / Mobile height')->numeric()->minValue(180)->maxValue(700)->default(300),
                                            Toggle::make('settings.slider_autoplay')->label('تشغيل تلقائي / Autoplay')->default(true),
                                            Toggle::make('settings.slider_loop')->label('تكرار مستمر / Loop')->default(true),
                                            Toggle::make('settings.slider_arrows')->label('أسهم التنقل / Arrows')->default(true),
                                            Toggle::make('settings.slider_dots')->label('نقاط التنقل / Dots')->default(true),
                                            Toggle::make('settings.slider_pause_hover')->label('توقف عند المرور / Pause on hover')->default(true),
                                            TextInput::make('settings.slider_interval')->label('المدة بين الصور (ms) / Interval')->numeric()->minValue(1500)->maxValue(20000)->default(4000),
                                            TextInput::make('settings.slider_speed')->label('سرعة الحركة (ms) / Speed')->numeric()->minValue(150)->maxValue(2000)->default(550),
                                            TextInput::make('settings.slider_radius')->label('استدارة الحواف / Radius')->numeric()->minValue(0)->maxValue(60)->default(20),
                                        ])->visible(fn (Get $get): bool => $get('settings.slider_enabled') !== false),
                                    ]),
                            ]),
                        Tab::make(__('landivo.sections.social_media'))
                            ->icon('heroicon-o-share')
                            ->schema([
                                Section::make(__('landivo.sections.social_media'))
                                    ->schema([
                                        Grid::make(4)->schema([
                                            Select::make('settings.social_icon_style')->label('ستايل الأيقونات / Icon theme')->options(['dark' => 'داكن فاخر / Luxury dark', 'circle' => 'ألوان المنصات / Brand colors', 'soft' => 'ناعم / Soft', 'outline' => 'إطار / Outline', 'glass' => 'زجاجي / Glass'])->default('dark'),
                                            Select::make('settings.social_icon_shape')->label('شكل الأيقونة / Shape')->options(['circle' => 'دائري / Circle', 'rounded' => 'مربع بحواف / Rounded square', 'square' => 'مربع / Square', 'pill' => 'طويل / Pill'])->default('circle'),
                                            TextInput::make('settings.social_icon_size')->label('حجم الأيقونة / Icon size')->numeric()->minValue(32)->maxValue(80)->default(54),
                                            TextInput::make('settings.social_icon_gap')->label('المسافة / Gap')->numeric()->minValue(4)->maxValue(40)->default(14),
                                        ]),
                                        Repeater::make('settings.social_media')->hiddenLabel()->schema([
                                            Select::make('platform')->label('المنصة / Platform')->options(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'whatsapp' => 'WhatsApp', 'x' => 'X', 'linkedin' => 'LinkedIn', 'telegram' => 'Telegram', 'snapchat' => 'Snapchat', 'pinterest' => 'Pinterest', 'website' => 'Website', 'phone' => 'Phone'])->required(),
                                            Select::make('icon')->label('الأيقونة الجاهزة / SVG icon')->options(['auto' => 'الأيقونة الأصلية للمنصة / Original icon', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'whatsapp' => 'WhatsApp', 'x' => 'X', 'linkedin' => 'LinkedIn', 'telegram' => 'Telegram', 'snapchat' => 'Snapchat', 'pinterest' => 'Pinterest', 'globe' => 'Website', 'phone' => 'Phone'])->default('auto'),
                                            FileUpload::make('icon_path')->label('صورة الأيقونة / Custom icon')->image()->disk('public')->directory('landing-pages/social-icons')->visibility('public')->imageEditor()->openable()->downloadable(),
                                            TextInput::make('url')->label(__('landivo.builder.link'))->url()->required(),
                                            ColorPicker::make('color')->label(__('landivo.builder.color'))->helperText('اتركه فارغاً لاستخدام اللون الأصلي للمنصة.'),
                                            Toggle::make('is_active')->label(__('landivo.editor.visible'))->default(true),
                                        ])->columns(5)->addActionLabel(__('landivo.builder.add_social')),
                                    ]),
                            ]),
                        Tab::make('شريط المتجر / Store Ticker')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                Section::make('شريط الأخبار والإعلانات / Announcement ticker')
                                    ->description('شريط متحرك ومتعدد اللغات للعروض، الشحن، التنبيهات والروابط المهمة. يظهر أعلى الصفحة أو بعد الفوتر.')
                                    ->schema([
                                        Toggle::make('settings.store_ticker.enabled')
                                            ->label('تفعيل الشريط / Enable ticker')
                                            ->helperText('فعّل هذا الخيار لإظهار الشريط في صفحة الهبوط. تبقى الأخبار محفوظة عند إيقافه. / Enable to display the ticker; items remain saved when disabled.')
                                            ->default(false)
                                            ->live(),
                                        Grid::make(4)->schema([
                                            Select::make('settings.store_ticker.placement')->label('الموقع / Placement')->options([
                                                'top' => 'أعلى صفحة الهبوط / Page top',
                                                'after_footer' => 'أسفل الفوتر / After footer',
                                            ])->default('top'),
                                            Select::make('settings.store_ticker.style')->label('التصميم / Style')->options([
                                                'solid' => 'لون موحد / Solid',
                                                'gradient' => 'تدرج فاخر / Gradient',
                                                'dark' => 'داكن فاخر / Luxury dark',
                                                'outline' => 'إطار خفيف / Outline',
                                                'glass' => 'زجاجي / Glass',
                                            ])->default('gradient'),
                                            Select::make('settings.store_ticker.direction')->label('اتجاه الحركة / Movement')->options([
                                                'left' => 'نحو اليسار / To left',
                                                'right' => 'نحو اليمين / To right',
                                            ])->default('left'),
                                            Select::make('settings.store_ticker.font_family')->label('الخط / Font')->options([
                                                'inherit' => 'خط الصفحة / Page font',
                                                'cairo' => 'Cairo',
                                                'tajawal' => 'Tajawal',
                                                'inter' => 'Inter',
                                                'noto' => 'Noto Sans Arabic',
                                            ])->default('inherit'),
                                            TextInput::make('settings.store_ticker.font_size')->label('حجم النص / Font size')->numeric()->minValue(11)->maxValue(28)->default(14),
                                            Select::make('settings.store_ticker.font_weight')->label('سماكة الخط / Weight')->options([500 => 'متوسط / Medium', 700 => 'عريض / Bold', 800 => 'عريض جدًا / Extra bold', 900 => 'أسود / Black'])->default(800),
                                            TextInput::make('settings.store_ticker.speed')->label('مدة الدورة بالثواني / Cycle seconds')->numeric()->minValue(6)->maxValue(180)->default(28)->helperText('رقم أقل = حركة أسرع.'),
                                            TextInput::make('settings.store_ticker.height')->label('ارتفاع الشريط / Height')->numeric()->minValue(36)->maxValue(100)->default(52),
                                            TextInput::make('settings.store_ticker.gap')->label('المسافة بين الأخبار / Gap')->numeric()->minValue(16)->maxValue(120)->default(48),
                                            Toggle::make('settings.store_ticker.pause_hover')->label('إيقاف عند المرور / Pause on hover')->default(true),
                                            Toggle::make('settings.store_ticker.full_width')->label('بعرض الشاشة / Full width')->default(true),
                                            Toggle::make('settings.store_ticker.show_separators')->label('فواصل بين العناصر / Separators')->default(true),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.store_ticker.enabled')),
                                        Grid::make(4)->schema([
                                            ColorPicker::make('settings.store_ticker.background_color')->label('الخلفية / Background')->default('#111827'),
                                            ColorPicker::make('settings.store_ticker.secondary_color')->label('لون التدرج / Gradient color')->default('#263A63'),
                                            ColorPicker::make('settings.store_ticker.text_color')->label('لون النص / Text color')->default('#FFFFFF'),
                                            ColorPicker::make('settings.store_ticker.accent_color')->label('لون الأيقونة والفاصل / Accent')->default('#F59E0B'),
                                            ColorPicker::make('settings.store_ticker.border_color')->label('لون الإطار / Border')->default('#E2E8F0'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.store_ticker.enabled')),
                                        Repeater::make('settings.store_ticker.items')
                                            ->label('محتوى الشريط / Ticker items')
                                            ->helperText('اكتب أخبار الشريط هنا أولًا، ثم فعّل ظهوره عندما يصبح جاهزًا. يمكنك إعداد المحتوى حتى لو كان الشريط غير مفعّل. / Compose ticker content here, then enable it when ready.')
                                            ->reorderable()
                                            ->collapsible()
                                            ->cloneable()
                                            ->defaultItems(1)
                                            ->itemLabel(fn (array $state): ?string => $state['text_ar'] ?? $state['text_en'] ?? 'خبر جديد')
                                            ->schema([
                                                Textarea::make('text_ar')->label('النص بالعربية / Arabic text')->placeholder('مثال: توصيل مجاني لجميع الإمارات')->rows(2),
                                                Textarea::make('text_en')->label('النص بالإنجليزية / English text')->placeholder('Example: Free delivery across the UAE')->rows(2),
                                                Select::make('icon')->label('الأيقونة / Icon')->options([
                                                    'none' => 'بدون / None',
                                                    'megaphone' => 'إعلان / Megaphone',
                                                    'sparkles' => 'مميز / Sparkles',
                                                    'truck' => 'توصيل / Delivery',
                                                    'gift' => 'هدية / Gift',
                                                    'tag' => 'عرض / Offer',
                                                    'shield' => 'موثوق / Trusted',
                                                    'phone' => 'اتصال / Contact',
                                                ])->default('sparkles'),
                                                TextInput::make('url')->label('الرابط الاختياري / Optional URL')->placeholder('/products أو https://...'),
                                                Toggle::make('highlight')->label('تمييز النص / Highlight')->default(false),
                                                Toggle::make('open_new_tab')->label('نافذة جديدة / New tab')->default(false),
                                                Toggle::make('is_active')->label('فعال / Active')->default(true),
                                            ])->columns(3)
                                            ->addActionLabel('إضافة خبر أو إعلان / Add ticker item'),
                                    ]),
                            ]),
                        Tab::make('الفوتر / Footer')
                            ->icon('heroicon-o-bars-3-bottom-left')
                            ->schema([
                                Section::make('فوتر صفحة الهبوط / Landing Page Footer')
                                    ->description('يظهر هذا القسم دائمًا في نهاية صفحة الهبوط بعد النموذج والتواصل الاجتماعي وجميع أقسام HTML.')
                                    ->schema([
                                        Toggle::make('settings.footer_enabled')->label('إظهار الفوتر / Show footer')->default(true)->live(),
                                        Grid::make(3)->schema([
                                            Select::make('settings.footer_style')->label('نمط الفوتر / Footer style')->options(['dark' => 'داكن فاخر / Luxury dark', 'light' => 'فاتح / Light', 'brand' => 'لون الهوية / Brand', 'glass' => 'زجاجي / Glass'])->default('dark'),
                                            Select::make('settings.footer_alignment')->label('محاذاة المحتوى / Alignment')->options(['start' => 'بداية / Start', 'center' => 'وسط / Center', 'end' => 'نهاية / End'])->default('center'),
                                            TextInput::make('settings.footer_spacing')->label('المسافة قبل الفوتر / Top spacing')->numeric()->minValue(8)->maxValue(120)->default(28),
                                            ColorPicker::make('settings.footer_background_color')->label('لون الخلفية / Background color'),
                                            ColorPicker::make('settings.footer_text_color')->label('لون النص / Text color'),
                                            ColorPicker::make('settings.footer_link_color')->label('لون الروابط / Link color'),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.footer_enabled')),
                                        Grid::make(2)->schema([
                                            CodeEditor::make('settings.footer_html_ar')->label('Body HTML للفوتر بالعربية / Arabic footer HTML')->language(CodeLanguage::Html),
                                            CodeEditor::make('settings.footer_html_en')->label('Body HTML للفوتر بالإنجليزية / English footer HTML')->language(CodeLanguage::Html),
                                        ])->visible(fn (Get $get): bool => (bool) $get('settings.footer_enabled')),
                                        Repeater::make('settings.footer_links')
                                            ->label('روابط الفوتر / Footer links')
                                            ->collapsible()
                                            ->reorderable()
                                            ->itemLabel(fn (array $state): ?string => $state['label_ar'] ?? $state['label_en'] ?? 'رابط جديد')
                                            ->schema([
                                                TextInput::make('label_ar')->label('اسم الرابط بالعربية / Arabic label')->required(),
                                                TextInput::make('label_en')->label('اسم الرابط بالإنجليزية / English label')->required(),
                                                TextInput::make('url')->label('الرابط / URL')->placeholder('/privacy-policy')->helperText('يقبل رابطًا داخليًا مثل /privacy-policy أو رابطًا كاملًا.')->required(),
                                                Toggle::make('open_new_tab')->label('فتح في نافذة جديدة / New tab')->default(false),
                                                Toggle::make('is_active')->label('فعال / Active')->default(true),
                                            ])->columns(5)
                                            ->addActionLabel('إضافة رابط للفوتر / Add footer link')
                                            ->visible(fn (Get $get): bool => (bool) $get('settings.footer_enabled')),
                                    ]),
                            ]),
                        Tab::make(__('landivo.sections.video'))
                            ->icon('heroicon-o-play-circle')
                            ->schema([
                                Section::make('فيديو YouTube / YouTube Video')->description('تضمين آمن ومتجاوب مع تحكم بالمظهر والتشغيل واللغة.')->schema([
                                    Toggle::make('settings.video_enabled')->label('تفعيل الفيديو / Enable video')->default(true)->live(),
                                    TextInput::make('settings.video_url')->label('رابط YouTube / YouTube URL')->url()->placeholder('https://www.youtube.com/watch?v=...')->columnSpanFull(),
                                    Grid::make(2)->schema([
                                        TextInput::make('settings.video_title_ar')->label('العنوان بالعربية / Arabic title'),
                                        TextInput::make('settings.video_title_en')->label('العنوان بالإنجليزية / English title'),
                                        Textarea::make('settings.video_description_ar')->label('الوصف بالعربية / Arabic description')->rows(2),
                                        Textarea::make('settings.video_description_en')->label('الوصف بالإنجليزية / English description')->rows(2),
                                    ]),
                                    Grid::make(4)->schema([
                                        Select::make('settings.video_style')->label('التصميم / Style')->options(['cinema' => 'سينمائي / Cinema', 'card' => 'بطاقة / Card', 'minimal' => 'بسيط / Minimal', 'full_bleed' => 'كامل / Full bleed'])->default('cinema'),
                                        Select::make('settings.video_ratio')->label('نسبة العرض / Ratio')->options(['16/9' => '16:9', '4/3' => '4:3', '9/16' => '9:16'])->default('16/9'),
                                        Select::make('settings.video_title_alignment')->label('محاذاة العنوان / Title alignment')->options(['start' => 'بداية / Start', 'center' => 'وسط / Center', 'end' => 'نهاية / End'])->default('center'),
                                        TextInput::make('settings.video_radius')->label('استدارة الحواف / Radius')->numeric()->minValue(0)->maxValue(60)->default(20),
                                        Toggle::make('settings.video_autoplay')->label('تشغيل تلقائي / Autoplay')->default(false),
                                        Toggle::make('settings.video_muted')->label('بدون صوت / Muted')->default(false),
                                        Toggle::make('settings.video_loop')->label('تكرار / Loop')->default(false),
                                        Toggle::make('settings.video_controls')->label('أزرار التحكم / Controls')->default(true),
                                        Toggle::make('settings.video_privacy')->label('وضع الخصوصية / Privacy mode')->default(true),
                                        ColorPicker::make('settings.video_background')->label('لون الخلفية / Background')->default('#0f172a'),
                                    ]),
                                ]),
                            ]),
                        Tab::make('الأكورديون / Accordion')
                            ->icon('heroicon-o-bars-3')
                            ->schema([
                                Section::make('الأكورديون / Accordion')->description('أسئلة وأوصاف قابلة للفتح، متعددة اللغات وبأكثر من تصميم.')->schema([
                                    Toggle::make('settings.accordion_enabled')->label('تفعيل الأكورديون / Enable accordion')->default(false)->live(),
                                    Grid::make(2)->schema([
                                        TextInput::make('settings.accordion_title_ar')->label('العنوان بالعربية / Arabic title'),
                                        TextInput::make('settings.accordion_title_en')->label('العنوان بالإنجليزية / English title'),
                                    ]),
                                    Grid::make(4)->schema([
                                        Select::make('settings.accordion_style')->label('التصميم / Style')->options(['clean' => 'نظيف / Clean', 'cards' => 'بطاقات / Cards', 'bordered' => 'حدود / Bordered', 'dark' => 'داكن / Dark', 'brand' => 'لون الهوية / Brand'])->default('cards'),
                                        Select::make('settings.accordion_icon')->label('الأيقونة / Icon')->options(['plus' => 'زائد / Plus', 'chevron' => 'سهم / Chevron', 'number' => 'ترقيم / Number'])->default('plus'),
                                        Select::make('settings.accordion_title_alignment')->label('محاذاة العنوان / Alignment')->options(['start' => 'بداية / Start', 'center' => 'وسط / Center', 'end' => 'نهاية / End'])->default('start'),
                                        Toggle::make('settings.accordion_allow_multiple')->label('فتح أكثر من عنصر / Multiple open')->default(false),
                                        Toggle::make('settings.accordion_first_open')->label('فتح أول عنصر / First open')->default(true),
                                        ColorPicker::make('settings.accordion_accent')->label('لون التمييز / Accent')->default('#4f46e5'),
                                        ColorPicker::make('settings.accordion_background')->label('لون الخلفية / Background')->default('#ffffff'),
                                        ColorPicker::make('settings.accordion_text_color')->label('لون النص / Text')->default('#172033'),
                                    ]),
                                    Repeater::make('settings.accordion_items')->label('العناصر / Items')->reorderable()->collapsible()->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? $state['title_en'] ?? null)->schema([
                                        TextInput::make('title_ar')->label('العنوان بالعربية')->required(),
                                        TextInput::make('title_en')->label('English title')->required(),
                                        RichEditor::make('content_ar')->label('المحتوى بالعربية')->columnSpanFull(),
                                        RichEditor::make('content_en')->label('English content')->columnSpanFull(),
                                        Toggle::make('is_active')->label('فعال / Active')->default(true),
                                    ])->columns(2)->addActionLabel('إضافة عنصر / Add item'),
                                ]),
                            ]),
                        Tab::make('صفحة الشكر / Thank You Page')
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Section::make('صفحة الشكر / Thank You Page')
                                    ->schema([
                                        Select::make('settings.thank_you_mode')->label(__('landivo.thank_you.mode'))->options(['internal' => __('landivo.thank_you.internal'), 'external' => __('landivo.thank_you.external')])->default('internal')->live(),
                                        Grid::make(2)->schema([
                                            TextInput::make('settings.thank_you_title_ar')->label(__('landivo.thank_you.title_ar')),
                                            TextInput::make('settings.thank_you_title_en')->label(__('landivo.thank_you.title_en')),
                                            TextInput::make('settings.thank_you_button_ar')->label(__('landivo.thank_you.button_ar')),
                                            TextInput::make('settings.thank_you_button_en')->label(__('landivo.thank_you.button_en')),
                                        ]),
                                        Grid::make(2)->schema([
                                            Textarea::make('settings.thank_you_message_ar')->label(__('landivo.thank_you.message_ar'))->rows(4),
                                            Textarea::make('settings.thank_you_message_en')->label(__('landivo.thank_you.message_en'))->rows(4),
                                        ]),
                                        Grid::make(2)->schema([
                                            TextInput::make('settings.thank_you_countdown')->label(__('landivo.thank_you.countdown'))->numeric()->minValue(0)->default(0),
                                            TextInput::make('settings.thank_you_redirect_url')->label(__('landivo.thank_you.redirect_url'))->url()->visible(fn (Get $get): bool => $get('settings.thank_you_mode') === 'external'),
                                        ]),
                                        Grid::make(2)->schema([
                                            FileUpload::make('settings.thank_you_image_ar')->label(__('landivo.thank_you.image_ar'))->image()->disk('public')->directory('landing-pages/thank-you'),
                                            FileUpload::make('settings.thank_you_image_en')->label(__('landivo.thank_you.image_en'))->image()->disk('public')->directory('landing-pages/thank-you'),
                                        ]),
                                        Grid::make(2)->schema([
                                            Textarea::make('settings.thank_you_head_code')->label(__('landivo.thank_you.head_code'))->rows(8),
                                            Textarea::make('settings.thank_you_body_code')->label(__('landivo.thank_you.body_code'))->rows(8),
                                        ]),
                                    ]),
                            ]),
                        Tab::make('أقسام HTML / HTML Blocks')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Section::make('محرر أقسام HTML')
                                    ->description('أضف أي عدد من أقسام HTML، خصصها لكل لغة، وحدد مكان ظهور كل قسم في صفحة الهبوط.')
                                    ->schema([
                                        Repeater::make('settings.html_blocks')
                                            ->hiddenLabel()
                                            ->reorderable()
                                            ->collapsible()
                                            ->cloneable()
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'HTML Block')
                                            ->schema([
                                                Grid::make(4)->schema([
                                                    TextInput::make('name')->label('اسم القسم')->required()->placeholder('مثال: بانر العرض')->live(onBlur: true),
                                                    TextInput::make('key')->label('المفتاح الداخلي')->required()->alphaDash()->placeholder('offer_banner')->helperText('يُستخدم لتمييز القسم داخل ترتيب الأقسام.')->live(onBlur: true),
                                                    Select::make('position')->label('مكان الظهور')->options([
                                                        '-10' => 'قبل رأس الصفحة',
                                                        '2' => 'بعد رأس الصفحة',
                                                        '7' => 'بعد المنتج والأسعار',
                                                        '12' => 'بعد العداد',
                                                        '38' => 'قبل نموذج الطلب',
                                                        '42' => 'بعد نموذج الطلب',
                                                        '48' => 'قبل التواصل الاجتماعي',
                                                        '52' => 'بعد التواصل الاجتماعي',
                                                        '90' => 'قبل الفوتر',
                                                    ])->default('42')->required(),
                                                    Toggle::make('is_active')->label('إظهار القسم')->default(true),
                                                ]),
                                                Grid::make(2)->schema([
                                                    Select::make('container_style')->label('شكل الحاوية')->options(['none' => 'بدون حاوية', 'card' => 'بطاقة بيضاء', 'full' => 'عرض كامل'])->default('none'),
                                                    TextInput::make('custom_class')->label('CSS Class اختياري')->alphaDash(),
                                                ]),
                                                CodeEditor::make('html_ar')->label('HTML بالعربية')->language(CodeLanguage::Html)->columnSpanFull(),
                                                CodeEditor::make('html_en')->label('HTML بالإنجليزية')->language(CodeLanguage::Html)->columnSpanFull(),
                                            ])
                                            ->addActionLabel('إضافة قسم HTML')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make(__('landivo.builder.order'))
                            ->icon('heroicon-o-arrows-up-down')
                            ->schema([
                                Section::make(__('landivo.editor.sections'))
                                    ->description(__('landivo.builder.order_description'))
                                    ->schema([
                                        Repeater::make('settings.section_order')->hiddenLabel()->schema([
                                            Select::make('type')
                                                ->label(__('landivo.editor.section_type'))
                                                ->options(function (Get $get): array {
                                                    $sections = collect(LandingSectionType::cases())
                                                        ->mapWithKeys(fn (LandingSectionType $type): array => [$type->value => $type->label()]);
                                                    $htmlBlocks = collect($get('../../html_blocks') ?? [])
                                                        ->filter(fn (array $block): bool => filled($block['key'] ?? null))
                                                        ->mapWithKeys(fn (array $block): array => [
                                                            'html_block:'.$block['key'] => 'HTML — '.($block['name'] ?? $block['key']),
                                                        ]);

                                                    return $sections->merge($htmlBlocks)->all();
                                                })
                                                ->searchable()
                                                ->required(),
                                            Toggle::make('is_visible')->label(__('landivo.editor.visible'))->default(true),
                                        ])->columns(2)->addActionLabel(__('landivo.builder.add_section_order'))->reorderable(),
                                    ]),
                            ]),
                        Tab::make(__('landivo.editor.sections'))
                            ->icon('heroicon-o-squares-2x2')
                            ->visible(false)
                            ->schema([
                                Section::make(__('landivo.editor.page_sections'))
                                    ->description(__('landivo.editor.sections_description'))
                                    ->schema([
                                        Repeater::make('sections')
                                            ->hiddenLabel()
                                            ->relationship()
                                            ->schema([
                                                Select::make('type')->label(__('landivo.editor.section_type'))->options(collect(LandingSectionType::cases())->mapWithKeys(fn (LandingSectionType $type): array => [$type->value => $type->label()])->all())->required()->live(),
                                                TextInput::make('sort_order')->label(__('landivo.editor.order'))->numeric()->default(0),
                                                Toggle::make('is_visible')->label(__('landivo.editor.visible'))->default(true),
                                                TextInput::make('settings.title')->label(__('landivo.editor.section_title'))->placeholder(__('landivo.editor.optional'))->columnSpanFull(),
                                                Textarea::make('settings.subtitle')->label(__('landivo.editor.subtitle'))->rows(2)->columnSpanFull(),
                                                DateTimePicker::make('settings.ends_at')->label(__('landivo.editor.ends_at'))->visible(fn (Get $get): bool => $get('type') === 'countdown')->seconds(false),
                                                Select::make('settings.countdown_style')->label(__('landivo.editor.countdown_style'))->options(['cards' => __('landivo.editor.countdown_cards'), 'minimal' => __('landivo.editor.countdown_minimal')])->default('cards')->visible(fn (Get $get): bool => $get('type') === 'countdown'),
                                                ColorPicker::make('settings.countdown_color')->label(__('landivo.editor.countdown_color'))->default('#8a9b22')->visible(fn (Get $get): bool => $get('type') === 'countdown'),
                                                ColorPicker::make('settings.countdown_label_color')->label(__('landivo.editor.countdown_label_color'))->default('#667085')->visible(fn (Get $get): bool => $get('type') === 'countdown'),
                                                TextInput::make('settings.number')->label(__('landivo.editor.whatsapp_number'))->visible(fn (Get $get): bool => $get('type') === 'whatsapp')->tel(),
                                                TextInput::make('settings.label')->label(__('landivo.editor.button_label'))->visible(fn (Get $get): bool => in_array($get('type'), ['whatsapp', 'order_form'], true)),
                                                Repeater::make('settings.fields')
                                                    ->label(__('landivo.editor.form_fields'))
                                                    ->visible(fn (Get $get): bool => $get('type') === 'order_form')
                                                    ->schema([
                                                        Select::make('type')->label(__('landivo.editor.field_type'))->options(['text' => __('landivo.editor.type_text'), 'textarea' => __('landivo.editor.type_textarea'), 'select' => __('landivo.editor.type_select'), 'radio' => __('landivo.editor.type_radio'), 'checkbox' => __('landivo.editor.type_checkbox'), 'date' => __('landivo.editor.type_date')])->required()->live(),
                                                        TextInput::make('label')->label(__('landivo.editor.field_label'))->required(),
                                                        TextInput::make('key')->label(__('landivo.editor.field_key'))->required()->alphaDash(),
                                                        TextInput::make('placeholder')->label(__('landivo.editor.placeholder')),
                                                        Toggle::make('required')->label(__('landivo.editor.required'))->default(false),
                                                        Toggle::make('include_in_invoice')->label(__('landivo.editor.invoice'))->default(true),
                                                        Textarea::make('options')->label(__('landivo.editor.options'))->helperText(__('landivo.editor.options_help'))->rows(3)->visible(fn (Get $get): bool => in_array($get('type'), ['select', 'radio', 'checkbox'], true))->columnSpanFull(),
                                                    ])
                                                    ->columns(3)
                                                    ->addActionLabel(__('landivo.editor.add_field'))
                                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                                    ->columnSpanFull(),
                                                TextInput::make('settings.item_1')->label(__('landivo.editor.feature_one'))->visible(fn (Get $get): bool => $get('type') === 'features'),
                                                TextInput::make('settings.item_2')->label(__('landivo.editor.feature_two'))->visible(fn (Get $get): bool => $get('type') === 'features'),
                                                TextInput::make('settings.item_3')->label(__('landivo.editor.feature_three'))->visible(fn (Get $get): bool => $get('type') === 'features'),
                                                FileUpload::make('settings.images')->label(__('landivo.editor.section_images'))->visible(fn (Get $get): bool => in_array($get('type'), ['product_gallery', 'gallery'], true))->image()->multiple()->disk('public')->directory('landing-pages')->reorderable(),
                                                TextInput::make('settings.video_url')->label(__('landivo.editor.video_url'))->url()->visible(fn (Get $get): bool => $get('type') === 'video'),
                                            ])
                                            ->columns(3)
                                            ->addActionLabel(__('landivo.editor.add_section'))
                                            ->itemLabel(fn (array $state): ?string => $state['type'] ?? null)
                                            ->orderColumn('sort_order')
                                            ->reorderable(),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }
}
