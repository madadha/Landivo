<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\LandingPage;
use App\Models\LandingPageSection;
use App\Models\LandingPageTranslation;
use App\Models\MediaAsset;
use App\Models\MarketingPopup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\ProductVariantTranslation;
use App\Models\Review;
use App\Models\SitePage;
use App\Models\SitePageTranslation;
use App\Models\User;
use App\Support\AuditableObserver;
use App\Support\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            Account::class, User::class, Customer::class,
            LandingPage::class, LandingPageTranslation::class, LandingPageSection::class,
            Product::class, ProductTranslation::class, ProductVariant::class,
            ProductVariantTranslation::class, ProductMedia::class,
            Order::class, OrderItem::class, OrderStatus::class, Review::class,
            SitePage::class, SitePageTranslation::class, ContactMessage::class, MediaAsset::class,
            MarketingPopup::class,
        ] as $model) {
            $model::observe(AuditableObserver::class);
        }

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                app(AuditLogger::class)->authentication($event->user, 'login');
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user instanceof User) {
                app(AuditLogger::class)->authentication($event->user, 'logout');
            }
        });
    }
}
