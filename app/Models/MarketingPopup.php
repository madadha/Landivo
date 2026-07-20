<?php

namespace App\Models;

use App\MarketingPopupTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingPopup extends Model
{
    protected $fillable = [
        'account_id', 'internal_name', 'template', 'eyebrow_ar', 'eyebrow_en',
        'title_ar', 'title_en', 'description_ar', 'description_en',
        'button_text_ar', 'button_text_en', 'button_url', 'open_new_tab',
        'desktop_image', 'mobile_image', 'page_scope', 'target_paths', 'locale',
        'device', 'trigger_type', 'delay_seconds', 'scroll_percentage', 'frequency',
        'priority', 'starts_at', 'ends_at', 'background_color', 'text_color',
        'button_color', 'button_text_color', 'overlay_color', 'border_radius',
        'max_width', 'allow_close', 'close_on_backdrop', 'is_active',
        'impressions_count', 'clicks_count',
    ];

    protected function casts(): array
    {
        return [
            'template' => MarketingPopupTemplate::class,
            'target_paths' => 'array',
            'open_new_tab' => 'boolean',
            'allow_close' => 'boolean',
            'close_on_backdrop' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'delay_seconds' => 'integer',
            'scroll_percentage' => 'integer',
            'priority' => 'integer',
            'border_radius' => 'integer',
            'max_width' => 'integer',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $query): Builder => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query): Builder => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function matchesPath(string $path): bool
    {
        $path = '/'.ltrim($path, '/');

        return match ($this->page_scope) {
            'homepage' => $path === '/',
            'landing_pages' => str_starts_with($path, '/l/'),
            'site_pages' => ! ($path === '/' || str_starts_with($path, '/l/')),
            'selected' => collect($this->target_paths ?? [])->contains(
                fn (string $target): bool => rtrim('/'.ltrim($target, '/'), '/') === rtrim($path, '/'),
            ),
            default => true,
        };
    }

    public function localized(string $field, string $locale): ?string
    {
        return $this->{$field.'_'.$locale} ?: $this->{$field.'_ar'} ?: $this->{$field.'_en'};
    }
}
