<?php

namespace App\Models;

use App\LandingPageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPage extends Model
{
    protected $fillable = ['account_id', 'product_id', 'product_variant_id', 'slug', 'template', 'status', 'default_locale', 'published_at', 'settings', 'track_inventory', 'stock_quantity', 'low_stock_threshold'];

    protected function casts(): array
    {
        return ['status' => LandingPageStatus::class, 'published_at' => 'datetime', 'settings' => 'array', 'track_inventory' => 'boolean', 'stock_quantity' => 'integer', 'low_stock_threshold' => 'integer'];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /** @return HasMany<LandingPageTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(LandingPageTranslation::class);
    }

    /** @return HasMany<LandingPageSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(LandingPageSection::class)->orderBy('sort_order');
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
