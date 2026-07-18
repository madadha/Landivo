<?php

namespace App\Models;

use App\LandingSectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageSection extends Model
{
    protected $fillable = ['landing_page_id', 'type', 'sort_order', 'is_visible', 'settings'];

    protected function casts(): array
    {
        return ['type' => LandingSectionType::class, 'is_visible' => 'boolean', 'settings' => 'array'];
    }

    /** @return BelongsTo<LandingPage, $this> */
    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }
}
