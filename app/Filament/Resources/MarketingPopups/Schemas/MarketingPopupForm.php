<?php

namespace App\Filament\Resources\MarketingPopups\Schemas;

use App\MarketingPopupTemplate;
use App\Models\LandingPage;
use App\Models\SitePage;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MarketingPopupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('محتوى النافذة')
                ->description('اكتب محتوى مستقلًا لكل لغة واختر القالب الأنسب للعرض.')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('internal_name')->label('الاسم الداخلي')->required()->maxLength(160)->placeholder('عرض ترحيبي للزوار الجدد'),
                        Select::make('template')->label('التصميم')->options(MarketingPopupTemplate::options())->required()->default('split_offer')->native(false),
                        Toggle::make('is_active')->label('مفعّلة')->default(true)->inline(false),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('eyebrow_ar')->label('النص العلوي بالعربية')->placeholder('عرض حصري'),
                        TextInput::make('eyebrow_en')->label('Eyebrow in English')->placeholder('Exclusive offer'),
                        TextInput::make('title_ar')->label('العنوان بالعربية')->required(),
                        TextInput::make('title_en')->label('Title in English'),
                        Textarea::make('description_ar')->label('الوصف بالعربية')->rows(4),
                        Textarea::make('description_en')->label('Description in English')->rows(4),
                    ]),
                    Grid::make(2)->schema([
                        FileUpload::make('desktop_image')->label('صورة الديسكتوب')->image()->disk('public')->directory('marketing-popups')->imageEditor()->openable(),
                        FileUpload::make('mobile_image')->label('صورة الموبايل')->image()->disk('public')->directory('marketing-popups')->imageEditor()->openable()->helperText('اختيارية؛ تُستخدم صورة الديسكتوب عند تركها فارغة.'),
                    ]),
                ]),

            Section::make('زر الإجراء')
                ->description('يمكن توجيه الزائر إلى منتج، صفحة هبوط، واتساب أو أي رابط خارجي.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('button_text_ar')->label('نص الزر بالعربية')->placeholder('اكتشف العرض'),
                        TextInput::make('button_text_en')->label('Button text in English')->placeholder('Discover offer'),
                        TextInput::make('button_url')->label('رابط الزر')->placeholder('/l/extravirgin2')->columnSpanFull(),
                        Toggle::make('open_new_tab')->label('فتح الرابط في تبويب جديد')->default(false),
                    ]),
                ]),

            Section::make('الجدولة والاستهداف')
                ->description('حدد أين ومتى ولمن تظهر النافذة، مع أولوية عند وجود أكثر من نافذة مناسبة.')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('page_scope')->label('الصفحات المستهدفة')->options([
                            'all' => 'جميع صفحات الموقع',
                            'homepage' => 'الصفحة الرئيسية فقط',
                            'landing_pages' => 'جميع صفحات الهبوط',
                            'site_pages' => 'صفحات الموقع والمنتجات',
                            'selected' => 'صفحات محددة',
                        ])->default('all')->required()->live()->native(false),
                        Select::make('locale')->label('اللغة')->options(['all' => 'جميع اللغات', 'ar' => 'العربية', 'en' => 'English'])->default('all')->required()->native(false),
                        Select::make('device')->label('الجهاز')->options(['all' => 'جميع الأجهزة', 'desktop' => 'ديسكتوب فقط', 'mobile' => 'موبايل وتابلت'])->default('all')->required()->native(false),
                    ]),
                    Select::make('target_paths')
                        ->label('اختر الصفحات')
                        ->multiple()
                        ->searchable()
                        ->options(fn (): array => self::pageOptions())
                        ->visible(fn (Get $get): bool => $get('page_scope') === 'selected')
                        ->required(fn (Get $get): bool => $get('page_scope') === 'selected')
                        ->helperText('اختر صفحة أو أكثر من القائمة.'),
                    Grid::make(3)->schema([
                        Select::make('trigger_type')->label('وقت الظهور')->options([
                            'delay' => 'بعد عدد من الثواني',
                            'scroll' => 'بعد تمرير نسبة من الصفحة',
                            'exit_intent' => 'عند محاولة مغادرة الصفحة',
                        ])->default('delay')->required()->live()->native(false),
                        TextInput::make('delay_seconds')->label('التأخير بالثواني')->numeric()->minValue(0)->maxValue(300)->default(2)->visible(fn (Get $get): bool => $get('trigger_type') === 'delay'),
                        TextInput::make('scroll_percentage')->label('نسبة التمرير %')->numeric()->minValue(5)->maxValue(95)->default(40)->visible(fn (Get $get): bool => $get('trigger_type') === 'scroll'),
                        Select::make('frequency')->label('تكرار الظهور')->options([
                            'always' => 'في كل زيارة للصفحة',
                            'once_session' => 'مرة واحدة في الجلسة',
                            'once_day' => 'مرة كل 24 ساعة',
                            'once_week' => 'مرة كل 7 أيام',
                            'once_ever' => 'مرة واحدة فقط للمتصفح',
                        ])->default('once_day')->required()->native(false),
                        TextInput::make('priority')->label('الأولوية')->numeric()->minValue(0)->maxValue(1000)->default(50)->helperText('الرقم الأعلى يظهر أولًا.'),
                    ]),
                    Grid::make(2)->schema([
                        DateTimePicker::make('starts_at')->label('تاريخ البداية')->seconds(false),
                        DateTimePicker::make('ends_at')->label('تاريخ النهاية')->seconds(false)->after('starts_at'),
                    ]),
                ]),

            Section::make('التصميم والسلوك')
                ->description('خصص الألوان والحجم والإغلاق بما يتوافق مع هوية متجرك.')
                ->schema([
                    Grid::make(5)->schema([
                        ColorPicker::make('background_color')->label('الخلفية')->default('#FFFFFF'),
                        ColorPicker::make('text_color')->label('النص')->default('#172033'),
                        ColorPicker::make('button_color')->label('الزر')->default('#8A9B22'),
                        ColorPicker::make('button_text_color')->label('نص الزر')->default('#FFFFFF'),
                        ColorPicker::make('overlay_color')->label('تعتيم الخلفية')->default('#0F172A'),
                    ]),
                    Grid::make(4)->schema([
                        TextInput::make('max_width')->label('أقصى عرض px')->numeric()->minValue(320)->maxValue(1400)->default(820),
                        TextInput::make('border_radius')->label('استدارة الحواف px')->numeric()->minValue(0)->maxValue(80)->default(28),
                        Toggle::make('allow_close')->label('إظهار زر الإغلاق')->default(true)->inline(false),
                        Toggle::make('close_on_backdrop')->label('الإغلاق عند ضغط الخلفية')->default(true)->inline(false),
                    ]),
                ]),
        ]);
    }

    private static function pageOptions(): array
    {
        $accountId = auth()->user()?->account_id;
        $options = ['/' => 'الصفحة الرئيسية', '/products' => 'صفحة المنتجات'];

        foreach (LandingPage::query()->where('account_id', $accountId)->orderBy('slug')->pluck('slug') as $slug) {
            $options['/l/'.$slug] = 'صفحة هبوط: '.$slug;
        }

        foreach (SitePage::query()->where('account_id', $accountId)->orderBy('slug')->pluck('slug') as $slug) {
            $options['/'.$slug] = 'صفحة موقع: '.$slug;
        }

        return $options;
    }
}
