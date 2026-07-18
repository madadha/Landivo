<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderActivity extends Model
{
    protected $fillable = ['order_id', 'user_id', 'type', 'body', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => AsArrayObject::class];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
