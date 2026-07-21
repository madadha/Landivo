<?php

namespace App\Filament\Resources\ThankYouPages\Schemas;

use App\Models\ThankYouPage;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ThankYouPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('هوية صفحة الشكر')
                ->description('أنشئ عددًا غير محدود من صفحات الشكر المستقلة، ولكل صفحة رابط مباشر خاص بها.')
                ->icon('heroicon-o-check-circle')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('internal_name')->label('الاسم الداخلي')->required()->maxLength(160)->placeholder('مثال: شكر حملة زيت الزيتون'),
                        TextInput::make('slug')->label('المعرّف المختصر للرابط')->required()->alphaDash()->maxLength(100)->prefix('/thank-you/')->helperText('استخدم حروفًا إنجليزية وأرقامًا وشرطة فقط.'),
                        Select::make('default_locale')->label('اللغة الافتراضية')->options(['ar' => 'العربية', 'en' => 'English'])->default('ar')->required()->native(false),
                        Toggle::make('is_active')->label('الصفحة مفعّلة')->default(true)->inline(false),
                    ]),
                    Placeholder::make('public_url')
                        ->label('الرابط المباشر القابل للنسخ')
                        ->content(fn (?ThankYouPage $record): HtmlString => $record
                            ? new HtmlString('<div dir="ltr" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:12px 14px;border:1px solid #dbe3ef;border-radius:12px;background:#f8fafc"><code style="user-select:all;font-weight:800;color:#0f766e">'.e($record->publicUrl()).'</code><a href="'.e($record->publicUrl()).'" target="_blank" rel="noopener" style="font-weight:800;color:#2563eb">فتح الصفحة ↗</a></div>')
                            : new HtmlString('<span style="color:#64748b">سيظهر الرابط المباشر بعد حفظ الصفحة.</span>')),
                ]),

            Section::make('المحتوى متعدد اللغات')
                ->description('لن يظهر للزائر إلا محتوى اللغة الحالية، مع الرجوع للغة الافتراضية عند غياب الترجمة.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('title_ar')->label('العنوان بالعربية')->required()->default('شكرًا لك، تم استلام طلبك'),
                        TextInput::make('title_en')->label('Title in English')->default('Thank you, your request was received'),
                        Textarea::make('message_ar')->label('التفاصيل بالعربية')->rows(5)->default('سيتواصل معك فريقنا في أقرب وقت لتأكيد التفاصيل.'),
                        Textarea::make('message_en')->label('Details in English')->rows(5)->default('Our team will contact you shortly to confirm the details.'),
                        TextInput::make('button_text_ar')->label('نص الزر بالعربية')->default('متابعة'),
                        TextInput::make('button_text_en')->label('Button text in English')->default('Continue'),
                    ]),
                    Grid::make(2)->schema([
                        FileUpload::make('image_ar')->label('الصورة العربية')->image()->disk('public')->directory('thank-you-pages')->imageEditor()->openable(),
                        FileUpload::make('image_en')->label('English image')->image()->disk('public')->directory('thank-you-pages')->imageEditor()->openable(),
                    ]),
                ]),

            Section::make('التحويل بعد الشكر')
                ->description('ضع رابط إنستغرام أو فيسبوك أو موقعك، وحدد مدة التحويل. اترك الرابط فارغًا لتعطيل الزر والتحويل التلقائي.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('redirect_url')->label('رابط الزر والتحويل')->url()->placeholder('https://www.instagram.com/your-page/'),
                        TextInput::make('countdown_seconds')->label('مدة التحويل التلقائي بالثواني')->numeric()->minValue(0)->maxValue(86400)->default(0)->helperText('ضع 0 لإيقاف التحويل التلقائي مع إبقاء الزر.'),
                    ]),
                ]),

            Section::make('التصميم والهوية البصرية')
                ->description('تصميم متجاوب للموبايل والحاسوب مع تحكم كامل بالألوان والخط والمحاذاة.')
                ->schema([
                    Grid::make(4)->schema([
                        Select::make('template')->label('القالب')->options(['premium' => 'احترافي', 'celebration' => 'احتفالي', 'minimal' => 'بسيط'])->default('premium')->required()->native(false),
                        Select::make('font_family')->label('الخط')->options(['cairo' => 'Cairo', 'tajawal' => 'Tajawal', 'inter' => 'Inter', 'noto' => 'Noto Sans Arabic'])->default('cairo')->required()->native(false),
                        Select::make('alignment')->label('محاذاة المحتوى')->options(['right' => 'يمين', 'center' => 'وسط', 'left' => 'يسار'])->default('center')->required()->native(false),
                        TextInput::make('border_radius')->label('استدارة الحواف')->numeric()->minValue(0)->maxValue(80)->default(28)->suffix('px'),
                    ]),
                    Grid::make(3)->schema([
                        ColorPicker::make('background_color')->label('خلفية الصفحة')->default('#F6F7FB'),
                        ColorPicker::make('card_color')->label('خلفية البطاقة')->default('#FFFFFF'),
                        ColorPicker::make('title_color')->label('لون العنوان')->default('#172033'),
                        ColorPicker::make('text_color')->label('لون النص')->default('#667085'),
                        ColorPicker::make('button_color')->label('لون الزر')->default('#172033'),
                        ColorPicker::make('button_text_color')->label('لون نص الزر')->default('#FFFFFF'),
                    ]),
                    Textarea::make('custom_css')->label('CSS إضافي')->rows(8)->placeholder('.thank-card { ... }')->helperText('اختياري للمطورين. يطبق داخل هذه الصفحة فقط.')->columnSpanFull(),
                ])->collapsible(),

            Section::make('أكواد التتبع والحملات')
                ->description('أضف Pixel أو Google Tag Manager وأكواد التحويل. الرابط المباشر يبقى ثابتًا، ويمكن توليد رابط حملة من المفاتيح أدناه.')
                ->schema([
                    Repeater::make('tracking_keys')
                        ->label('مفاتيح التتبع')
                        ->schema([
                            TextInput::make('key')->label('المفتاح')->placeholder('utm_source')->required(),
                            TextInput::make('value')->label('القيمة')->placeholder('facebook')->required(),
                            TextInput::make('comment')->label('شرح')->placeholder('مصدر الحملة الإعلانية'),
                        ])
                        ->columns(3)
                        ->addActionLabel('إضافة مفتاح')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => filled($state['key'] ?? null) ? ($state['key'].' = '.($state['value'] ?? '')) : 'مفتاح جديد'),
                    Placeholder::make('campaign_url')
                        ->label('رابط الحملة الناتج')
                        ->content(fn (?ThankYouPage $record): HtmlString => $record
                            ? new HtmlString('<code dir="ltr" style="display:block;user-select:all;padding:12px 14px;border:1px solid #dbe3ef;border-radius:12px;background:#f8fafc;color:#0f766e;font-weight:800;overflow-wrap:anywhere">'.e($record->campaignUrl()).'</code>')
                            : new HtmlString('<span style="color:#64748b">احفظ الصفحة أولًا ليظهر رابط الحملة.</span>')),
                    Grid::make(2)->schema([
                        Textarea::make('head_code')->label('Head Code')->rows(10)->placeholder('<!-- Meta Pixel, GTM, analytics... -->'),
                        Textarea::make('body_code')->label('Body Code')->rows(10)->placeholder('<!-- Conversion event or custom HTML... -->'),
                    ]),
                ])->collapsible(),
        ]);
    }
}
