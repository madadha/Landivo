<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePageTranslation extends Model
{
    protected $fillable = ['site_page_id', 'locale', 'title', 'navigation_label', 'excerpt', 'content', 'hero_image', 'blocks', 'seo_title', 'seo_description'];

    protected function casts(): array
    {
        return ['blocks' => 'array'];
    }

    public function sitePage(): BelongsTo
    {
        return $this->belongsTo(SitePage::class);
    }
}
