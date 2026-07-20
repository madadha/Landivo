<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'option_values', 'price', 'compare_at_price', 'quantity', 'image_path', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'option_values' => 'array',
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'quantity' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductVariantTranslation::class);
    }

    public function translation(?string $locale = null): ?ProductVariantTranslation
    {
        $locale ??= app()->getLocale();
        $translations = $this->relationLoaded('translations') ? $this->translations : $this->translations()->get();

        return $translations->firstWhere('locale', $locale) ?? $translations->first();
    }

    public function label(): string
    {
        if ($name = $this->translation()?->name) {
            return $name.' ('.($this->sku ?: 'Variant').')';
        }

        $options = collect($this->option_values ?? [])
            ->map(fn ($value, $key): string => $key.': '.$value)
            ->implode(' / ');

        return trim(($this->sku ?: 'Variant').($options ? ' — '.$options : ''));
    }
}
