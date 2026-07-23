<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataTransfer extends Model
{
    protected $fillable = [
        'account_id', 'user_id', 'type', 'entity', 'status', 'source_path',
        'result_path', 'original_name', 'total_rows', 'processed_rows',
        'succeeded_rows', 'failed_rows', 'error_message', 'metadata',
        'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function progressPercentage(): int
    {
        if ($this->status === 'completed') {
            return 100;
        }

        if ($this->total_rows < 1) {
            return $this->status === 'processing' ? 5 : 0;
        }

        return min(99, (int) round(($this->processed_rows / $this->total_rows) * 100));
    }

    public function isRunning(): bool
    {
        return in_array($this->status, ['queued', 'processing'], true);
    }
}
