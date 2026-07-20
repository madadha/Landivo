<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePage extends Model
{
    protected $fillable = ['account_id', 'slug', 'template', 'status', 'show_in_header', 'show_in_footer', 'sort_order'];

    protected function casts(): array
    {
        return ['show_in_header' => 'boolean', 'show_in_footer' => 'boolean'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SitePageTranslation::class);
    }

    public function translation(?string $locale = null): ?SitePageTranslation
    {
        $locale ??= app()->getLocale();

        return $this->translations->firstWhere('locale', $locale) ?: $this->translations->first();
    }
}
