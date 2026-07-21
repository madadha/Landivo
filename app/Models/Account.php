<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'logo_path', 'favicon_path', 'company_details',
        'default_locale', 'phone_country_code', 'settings',
    ];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return HasMany<LandingPage, $this> */
    public function landingPages(): HasMany
    {
        return $this->hasMany(LandingPage::class);
    }

    /** @return HasMany<OrderStatus, $this> */
    public function orderStatuses(): HasMany
    {
        return $this->hasMany(OrderStatus::class);
    }

    /** @return HasMany<Customer, $this> */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function sitePages(): HasMany
    {
        return $this->hasMany(SitePage::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function marketingPopups(): HasMany
    {
        return $this->hasMany(MarketingPopup::class);
    }

    public function thankYouPages(): HasMany
    {
        return $this->hasMany(ThankYouPage::class);
    }
}
