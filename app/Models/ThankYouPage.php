<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThankYouPage extends Model
{
    protected $fillable = [
        'account_id', 'internal_name', 'slug', 'is_active', 'default_locale', 'template',
        'title_ar', 'title_en', 'message_ar', 'message_en', 'button_text_ar',
        'button_text_en', 'redirect_url', 'countdown_seconds', 'image_ar', 'image_en',
        'font_family', 'alignment', 'background_color', 'card_color', 'title_color',
        'text_color', 'button_color', 'button_text_color', 'border_radius', 'head_code',
        'body_code', 'custom_css', 'tracking_keys',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'countdown_seconds' => 'integer',
            'border_radius' => 'integer',
            'tracking_keys' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function localized(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $this->{$field.'_'.$locale}
            ?: $this->{$field.'_'.$this->default_locale}
            ?: $this->{$field.'_ar'}
            ?: $this->{$field.'_en'};
    }

    public function publicUrl(): string
    {
        return route('thank-you-pages.show', $this);
    }

    public function campaignUrl(): string
    {
        $query = collect($this->tracking_keys)
            ->filter(fn (array $item): bool => filled($item['key'] ?? null) && filled($item['value'] ?? null))
            ->mapWithKeys(fn (array $item): array => [(string) $item['key'] => (string) $item['value']])
            ->all();

        return $query ? $this->publicUrl().'?'.http_build_query($query) : $this->publicUrl();
    }
}
