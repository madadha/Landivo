<?php

namespace App\Providers\Filament;

use App\Filament\Pages\EditProfile;
use App\Filament\Pages\SystemSettings;
use App\Filament\Widgets\LeadsOverview;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(EditProfile::class)
            ->brandName(fn (): string => auth()->user()?->account?->name ?? 'Landivo')
            ->favicon(fn (): ?string => filled(auth()->user()?->account?->favicon_path)
                ? Storage::disk('public')->url(auth()->user()->account->favicon_path)
                : null)
            ->brandLogo(fn (): ?string => filled(auth()->user()?->account?->logo_path)
                ? Storage::disk('public')->url(auth()->user()->account->logo_path)
                : null)
            ->userMenuItems([
                'dashboard' => Action::make('dashboard')->label('لوحة التحكم')->icon(Heroicon::OutlinedHome)->url(fn (): string => route('filament.admin.pages.dashboard'))->sort(-10),
                'users' => Action::make('users')->label('المستخدمون')->icon(Heroicon::OutlinedUsers)->url(fn (): string => \App\Filament\Resources\Users\UserResource::getUrl())->sort(-5),
                'roles' => Action::make('roles')->label('الصلاحيات والأدوار')->icon(Heroicon::OutlinedShieldCheck)->url(fn (): string => \App\Filament\Resources\Roles\RoleResource::getUrl())->sort(0),
                'settings' => Action::make('system-settings')->label('إعدادات النظام')->icon(Heroicon::OutlinedCog6Tooth)->url(fn (): string => SystemSettings::getUrl())->sort(10),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                LeadsOverview::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
