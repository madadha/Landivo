<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorEvent extends Model
{
    protected $fillable = [
        'account_id', 'landing_page_id', 'product_id', 'event_type', 'path',
        'session_hash', 'ip_address', 'user_agent', 'referer', 'utm_parameters',
    ];

    protected function casts(): array
    {
        return ['utm_parameters' => 'array'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
