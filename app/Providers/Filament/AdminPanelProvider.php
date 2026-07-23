<?php

namespace App\Providers\Filament;

use App\Notifications\VerifyEmailAuthentication;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EditProfile;
use App\Filament\Pages\SystemSettings;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Account;
use App\Models\Order;
use App\Models\OrderStatus;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
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
            ->passwordReset()
            ->multiFactorAuthentication(
                EmailAuthentication::make()
                    ->codeExpiryMinutes(5)
                    ->codeNotification(VerifyEmailAuthentication::class),
            )
            ->profile(EditProfile::class)
            ->brandName(fn (): string => auth()->user()?->account?->name ?? Account::query()->value('name') ?? 'Landivo')
            ->favicon(fn (): ?string => filled(auth()->user()?->account?->favicon_path ?? Account::query()->value('favicon_path'))
                ? Storage::disk('public')->url(auth()->user()?->account?->favicon_path ?? Account::query()->value('favicon_path'))
                : null)
            ->brandLogo(fn (): ?string => filled(auth()->user()?->account?->logo_path ?? Account::query()->value('logo_path'))
                ? Storage::disk('public')->url(auth()->user()?->account?->logo_path ?? Account::query()->value('logo_path'))
                : null)
            ->brandLogoHeight('3.5rem')
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, function (): string {
                $accountId = auth()->user()?->account_id;
                $newStatus = OrderStatus::query()
                    ->where('account_id', $accountId)
                    ->where('slug', 'new')
                    ->first();
                $newOrders = $newStatus
                    ? Order::query()->where('account_id', $accountId)->whereNull('archived_at')->where('order_status_id', $newStatus->getKey())->count()
                    : 0;
                $ordersUrl = OrderResource::getUrl('index', $newStatus ? [
                    'filters' => ['order_status_id' => ['values' => [(string) $newStatus->getKey()]]],
                ] : []);

                return view('components.admin-order-notification', compact('newOrders', 'ordersUrl'))->render();
            })
            ->renderHook(PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE, fn (): string => view('components.admin-save-action')->render())
            ->userMenuItems([
                'dashboard' => Action::make('dashboard')->label('لوحة التحكم')->icon(Heroicon::OutlinedHome)->url(fn (): string => route('filament.admin.pages.dashboard'))->sort(-10),
                'users' => Action::make('users')->label('المستخدمون')->icon(Heroicon::OutlinedUsers)->url(fn (): string => UserResource::getUrl())->sort(-5),
                'roles' => Action::make('roles')->label('الصلاحيات والأدوار')->icon(Heroicon::OutlinedShieldCheck)->url(fn (): string => RoleResource::getUrl())->sort(0),
                'settings' => Action::make('system-settings')->label('إعدادات النظام')->icon(Heroicon::OutlinedCog6Tooth)->url(fn (): string => SystemSettings::getUrl())->sort(10),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                'المبيعات والطلبات',
                'العملاء والتواصل',
                'الكتالوج والمخزون',
                'صفحات الهبوط',
                'الموقع والمحتوى',
                'التسويق',
                'التقارير والتحليلات',
                'إدارة النظام',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
