<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAttachment extends Model
{
    protected $fillable = ['order_id', 'user_id', 'path', 'original_name', 'mime_type', 'size'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
