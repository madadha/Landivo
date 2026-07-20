<?php

namespace App\Filament\Resources\SitePages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SitePageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('إعدادات الصفحة')->description('تحكم بالرابط والقالب ومكان ظهور الصفحة في الموقع.')->schema([
                Grid::make(3)->schema([
                    TextInput::make('slug')->label('رابط الصفحة')->required()->alphaDash()->unique(ignoreRecord: true)->helperText('مثال: about-us أو privacy-policy'),
                    Select::make('template')->label('نوع الصفحة')->options(['content' => 'صفحة محتوى', 'products' => 'صفحة المنتجات', 'about' => 'من نحن', 'contact' => 'تواصل معنا', 'privacy' => 'سياسة الخصوصية', 'terms' => 'الأحكام والشروط'])->required()->default('content'),
                    Select::make('status')->label('الحالة')->options(['draft' => 'مسودة', 'published' => 'منشورة'])->required()->default('draft'),
                    TextInput::make('sort_order')->label('ترتيب الصفحة')->numeric()->default(0),
                    Toggle::make('show_in_header')->label('إظهار في القائمة الرئيسية')->default(true),
                    Toggle::make('show_in_footer')->label('إظهار في الفوتر')->default(true),
                ]),
            ]),
            Section::make('المحتوى متعدد اللغات')->description('أضف العربية والإنجليزية، ولكل لغة محتواها وصورتها وإعدادات SEO الخاصة بها.')->schema([
                Repeater::make('translations')->relationship()->label('اللغات')->schema([
                    Grid::make(2)->schema([
                        Select::make('locale')->label('اللغة')->options(['ar' => 'العربية', 'en' => 'English'])->required(),
                        TextInput::make('navigation_label')->label('الاسم في القائمة')->placeholder('يستخدم عنوان الصفحة إذا تركته فارغًا'),
                        TextInput::make('title')->label('عنوان الصفحة')->required(),
                        Textarea::make('excerpt')->label('وصف مختصر')->rows(2),
                        FileUpload::make('hero_image')->label('صورة الغلاف')->image()->disk('public')->directory('site-pages/heroes')->imageEditor()->columnSpanFull(),
                        RichEditor::make('content')->label('المحتوى الرئيسي')->columnSpanFull(),
                    ]),
                    Repeater::make('blocks')->label('منشئ الأقسام')->collapsed()->itemLabel(fn (array $state): string => $state['title'] ?? 'قسم جديد')->schema([
                        Select::make('type')->label('نوع القسم')->options(['content' => 'نص منسق', 'image' => 'صورة مع نص', 'features' => 'مميزات', 'cta' => 'دعوة لاتخاذ إجراء', 'html' => 'HTML مخصص'])->required()->default('content'),
                        TextInput::make('title')->label('عنوان القسم'),
                        RichEditor::make('body')->label('المحتوى')->columnSpanFull(),
                        FileUpload::make('image')->label('الصورة')->image()->disk('public')->directory('site-pages/blocks')->imageEditor(),
                        TextInput::make('button_label')->label('نص الزر'),
                        TextInput::make('button_url')->label('رابط الزر'),
                        Textarea::make('html')->label('HTML مخصص')->rows(6)->columnSpanFull(),
                    ])->columns(2)->columnSpanFull()->addActionLabel('إضافة قسم جديد'),
                    Grid::make(2)->schema([
                        TextInput::make('seo_title')->label('عنوان SEO')->maxLength(60),
                        Textarea::make('seo_description')->label('وصف SEO')->rows(2)->maxLength(160),
                    ]),
                ])->defaultItems(1)->collapsed()->columnSpanFull()->addActionLabel('إضافة لغة'),
            ]),
        ]);
    }
}
