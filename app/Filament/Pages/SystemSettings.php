<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SystemSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static ?string $navigationLabel = 'إعدادات النظام';
    protected static ?string $title = 'إعدادات النظام';
    protected static ?string $slug = 'system-settings';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.system-settings';
    public ?array $data = [];

    public function mount(): void
    {
        $account = auth()->user()->account;
        $this->form->fill([
            'name' => $account?->name, 'slug' => $account?->slug,
            'description' => $account?->description, 'logo_path' => $account?->logo_path,
            'favicon_path' => $account?->favicon_path,
            'company_details' => $account?->company_details,
            'default_locale' => $account?->default_locale ?? 'ar',
            'phone_country_code' => $account?->phone_country_code ?? '971',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('هوية الشركة / Company identity')->schema([
                FileUpload::make('logo_path')->label('شعار الشركة / Company logo')->image()->disk('public')->directory('accounts/logos')->imageEditor(),
                FileUpload::make('favicon_path')
                    ->label('أيقونة الموقع / Favicon')
                    ->image()
                    ->disk('public')
                    ->directory('accounts/favicon')
                    ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/jpeg'])
                    ->maxSize(2048)
                    ->helperText('ارفع favicon.ico أو صورة PNG مربعة، وستظهر بجانب رابط الموقع في المتصفح.'),
                TextInput::make('name')->label('اسم الشركة')->required(),
                TextInput::make('slug')->label('المعرّف الداخلي')->disabled(),
                Textarea::make('description')->label('وصف الشركة')->rows(3),
                Textarea::make('company_details')->label('تفاصيل الشركة')->rows(4),
            ]),
            Section::make('الإعدادات المحلية / Locale settings')->schema([
                Select::make('default_locale')->label('اللغة الافتراضية / Default language')->options(['ar' => 'العربية', 'en' => 'English'])->required(),
                TextInput::make('phone_country_code')->label('كود الدولة للهاتف / Phone country code')->prefix('+')->numeric()->required()->maxLength(6),
            ])->columns(2),
            ]);
    }

    public function save(): void
    {
        auth()->user()->account?->update($this->form->getState());
        Notification::make()->success()->title('تم حفظ إعدادات النظام')->send();
    }
}
