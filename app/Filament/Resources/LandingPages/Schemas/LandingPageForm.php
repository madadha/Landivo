<?php

namespace App\Filament\Resources\LandingPages\Schemas;

use App\Enums\FieldType;
use App\LandingPageStatus;
use App\LandingSectionType;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
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
                                Section::make('الفوتر / Footer')
                                    ->description('اكتب محتوى HTML للفوتر ليظهر أسفل صفحة الهبوط.')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('settings.footer_style')->label('نمط الفوتر / Footer style')->options(['dark' => 'داكن / Dark', 'light' => 'فاتح / Light', 'brand' => 'لون الهوية / Brand', 'glass' => 'زجاجي / Glass'])->default('dark'),
                                            Select::make('settings.footer_alignment')->label('محاذاة الفوتر / Alignment')->options(['start' => 'بداية / Start', 'center' => 'وسط / Center', 'end' => 'نهاية / End'])->default('center'),
                                            TextInput::make('settings.footer_spacing')->label('المسافة عن السوشيال / Social spacing')->numeric()->minValue(8)->maxValue(80)->default(28),
                                        ]),
                                        RichEditor::make('settings.footer_html_ar')
                                            ->label('محتوى الفوتر بالعربية / Arabic footer HTML')
                                            ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'bulletList', 'orderedList', 'h2', 'h3', 'blockquote', 'undo', 'redo'])
                                            ->columnSpanFull(),
                                        RichEditor::make('settings.footer_html_en')
                                            ->label('محتوى الفوتر بالإنجليزية / English footer HTML')
                                            ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'bulletList', 'orderedList', 'h2', 'h3', 'blockquote', 'undo', 'redo'])
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('دعم واتساب / WhatsApp Support')
                                    ->schema([
                                        Toggle::make('settings.show_whatsapp_support')->label('تفعيل زر واتساب العائم / Enable floating WhatsApp')->default(false)->live(),
                                        Grid::make(2)->schema([
                                            TextInput::make('settings.whatsapp_support_number')->label('رقم واتساب / WhatsApp number')->tel()->visible(fn (Get $get): bool => (bool) $get('settings.show_whatsapp_support')),
                                            TextInput::make('settings.whatsapp_support_message')->label('رسالة البداية / Initial message')->placeholder('مرحباً، أريد الاستفسار عن المنتج')->visible(fn (Get $get): bool => (bool) $get('settings.show_whatsapp_support')),
                                        ]),
                                    ]),
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
                                        ]),
                                    ]),
                            ]),
                        Tab::make(__('landivo.editor.design'))
                            ->icon('heroicon-o-swatch')
                            ->schema([
                                Section::make(__('landivo.editor.design_settings'))
                                    ->description(__('landivo.editor.design_description'))
                                    ->schema([
                                        Grid::make(4)->schema([
                                            Select::make('settings.font_family')->label(__('landivo.landing_pages.font_family'))->options(['cairo' => 'Cairo', 'tajawal' => 'Tajawal', 'inter' => 'Inter', 'noto' => 'Noto Sans Arabic'])->default('cairo'),
                                            TextInput::make('settings.heading_size')->label(__('landivo.landing_pages.heading_size'))->numeric()->minValue(24)->maxValue(72)->default(44),
                                            TextInput::make('settings.body_size')->label(__('landivo.landing_pages.body_size'))->numeric()->minValue(14)->maxValue(24)->default(18),
                                            ColorPicker::make('settings.primary_color')->label(__('landivo.landing_pages.primary_color'))->default('#4f46e5'),
                                        ]),
                                    ]),
                                Section::make(__('landivo.editor.visibility'))
                                    ->schema([
                                        Toggle::make('settings.show_price')->label(__('landivo.editor.show_price'))->default(true),
                                        Toggle::make('settings.show_compare_price')->label(__('landivo.editor.show_compare_price'))->default(true),
                                        Toggle::make('settings.show_order_form')->label(__('landivo.editor.show_order_form'))->default(true),
                                    ])->columns(3),
                                Section::make(__('landivo.editor.price_settings'))
                                    ->description(__('landivo.editor.price_description'))
                                    ->schema([
                                        Grid::make(4)->schema([
                                            ColorPicker::make('settings.current_price_color')->label(__('landivo.editor.current_price_color'))->default('#4f46e5'),
                                            ColorPicker::make('settings.compare_price_color')->label(__('landivo.editor.compare_price_color'))->default('#98a2b3'),
                                            TextInput::make('settings.current_price_size')->label(__('landivo.editor.current_price_size'))->numeric()->minValue(20)->maxValue(64)->default(32),
                                            TextInput::make('settings.compare_price_size')->label(__('landivo.editor.compare_price_size'))->numeric()->minValue(14)->maxValue(36)->default(20),
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
                                Section::make(__('landivo.builder.slider'))
                                    ->description(__('landivo.builder.slider_description'))
                                    ->schema([
                                        FileUpload::make('settings.slider_images')->label(__('landivo.editor.section_images'))->image()->multiple()->disk('public')->directory('landing-pages/sliders')->reorderable(),
                                        Grid::make(2)->schema([
                                            Toggle::make('settings.slider_autoplay')->label(__('landivo.builder.autoplay'))->default(true),
                                            TextInput::make('settings.slider_interval')->label(__('landivo.builder.interval'))->numeric()->default(4000),
                                        ]),
                                    ]),
                            ]),
                        Tab::make(__('landivo.sections.social_media'))
                            ->icon('heroicon-o-share')
                            ->schema([
                                Section::make(__('landivo.sections.social_media'))
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('settings.social_icon_style')->label('نمط الأيقونات / Icon style')->options(['circle' => 'دائري ملون / Color circles', 'soft' => 'ناعم / Soft', 'outline' => 'إطار / Outline', 'glass' => 'زجاجي / Glass', 'pill' => 'أزرار طويلة / Pills'])->default('circle'),
                                            TextInput::make('settings.social_icon_size')->label('حجم الأيقونة / Icon size')->numeric()->minValue(32)->maxValue(80)->default(54),
                                            TextInput::make('settings.social_icon_gap')->label('المسافة / Gap')->numeric()->minValue(4)->maxValue(40)->default(14),
                                        ]),
                                        Repeater::make('settings.social_media')->hiddenLabel()->schema([
                                            Select::make('platform')->options(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'whatsapp' => 'WhatsApp'])->required(),
                                            TextInput::make('url')->label(__('landivo.builder.link'))->url()->required(),
                                            ColorPicker::make('color')->label(__('landivo.builder.color'))->default('#4f46e5'),
                                            Toggle::make('is_active')->label(__('landivo.editor.visible'))->default(true),
                                        ])->columns(4)->addActionLabel(__('landivo.builder.add_social')),
                                    ]),
                            ]),
                        Tab::make(__('landivo.sections.video'))
                            ->icon('heroicon-o-play-circle')
                            ->schema([
                                Section::make(__('landivo.sections.video'))->schema([
                                    TextInput::make('settings.video_url')->label(__('landivo.editor.video_url'))->url(),
                                    TextInput::make('settings.video_label')->label(__('landivo.builder.button')),
                                ])->columns(2),
                            ]),
                        Tab::make(__('landivo.public.thank_you'))
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Section::make(__('landivo.public.thank_you'))
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
                        Tab::make(__('landivo.builder.order'))
                            ->icon('heroicon-o-arrows-up-down')
                            ->schema([
                                Section::make(__('landivo.editor.sections'))
                                    ->description(__('landivo.builder.order_description'))
                                    ->schema([
                                        Repeater::make('settings.section_order')->hiddenLabel()->schema([
                                            Select::make('type')->label(__('landivo.editor.section_type'))->options(collect(LandingSectionType::cases())->mapWithKeys(fn (LandingSectionType $type): array => [$type->value => $type->label()])->all())->required(),
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
