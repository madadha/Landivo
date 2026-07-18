<?php

namespace App\Models;

use App\ProductStatus;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['account_id', 'sku', 'price', 'compare_at_price', 'currency', 'quantity', 'status', 'primary_image_path', 'metadata'];

    protected function casts(): array
    {
        return ['status' => ProductStatus::class, 'price' => 'decimal:2', 'compare_at_price' => 'decimal:2', 'metadata' => AsArrayObject::class];
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
}
