<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = ['account_id', 'name', 'email', 'phone', 'subject', 'message', 'ip_address', 'is_read'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
