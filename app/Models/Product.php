<?php

namespace App\Models;

use App\ProductStatus;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'account_id',
        'sku',
        'price',
        'compare_at_price',
        'currency',
        'quantity',
        'status',
        'sort_order',
        'primary_image_path',
        'metadata',
        'options',
        'badge_is_active',
        'badge_text_ar',
        'badge_text_en',
        'badge_style',
        'badge_background_color',
        'badge_text_color',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'sort_order' => 'integer',
            'metadata' => AsArrayObject::class,
            'options' => 'array',
            'badge_is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return HasMany<ProductTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function localizedMedia(?string $locale = null): ?ProductMedia
    {
        $locale ??= app()->getLocale();
        $media = $this->relationLoaded('media') ? $this->media : $this->media()->where('is_active', true)->get();

        return $media->first(fn (ProductMedia $item): bool => $item->is_active && $item->locale === $locale)
            ?? $media->first(fn (ProductMedia $item): bool => $item->is_active && blank($item->locale))
            ?? $media->first(fn (ProductMedia $item): bool => $item->is_active);
    }

    public function badgeLabel(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? ($this->badge_text_en ?: $this->badge_text_ar ?: 'Featured offer')
            : ($this->badge_text_ar ?: $this->badge_text_en ?: 'عرض مميز');
    }

    public function hasVisibleBadge(?string $locale = null): bool
    {
        return $this->badge_is_active && filled($this->badgeLabel($locale));
    }
}
