<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MediaAsset extends Model
{
    protected $fillable = [
        'account_id', 'uploaded_by', 'disk', 'path', 'original_name', 'title', 'alt_text',
        'folder', 'mime_type', 'extension', 'category', 'size', 'width', 'height',
        'checksum', 'usage_count', 'usage_locations', 'file_exists', 'metadata', 'last_scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer', 'width' => 'integer', 'height' => 'integer',
            'usage_count' => 'integer', 'usage_locations' => 'array', 'file_exists' => 'boolean',
            'metadata' => 'array', 'last_scanned_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getPublicUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = max(0, (int) $this->size);
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1073741824) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format($bytes / 1073741824, 2).' GB';
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function refreshFileMetadata(): void
    {
        $disk = Storage::disk($this->disk);
        $exists = $disk->exists($this->path);
        $this->file_exists = $exists;
        $this->folder = trim(dirname($this->path), '.') ?: null;
        $this->extension = strtolower(pathinfo($this->path, PATHINFO_EXTENSION)) ?: null;

        if (! $exists) {
            $this->last_scanned_at = now();
            $this->saveQuietly();

            return;
        }

        $this->size = $disk->size($this->path);
        $this->original_name ??= basename($this->path);

        try {
            $this->mime_type = $disk->mimeType($this->path) ?: $this->mime_type;
        } catch (Throwable) {
            // Keep the previously detected MIME type.
        }

        $this->category = match (true) {
            str_starts_with((string) $this->mime_type, 'image/') => 'image',
            str_starts_with((string) $this->mime_type, 'video/') => 'video',
            str_starts_with((string) $this->mime_type, 'audio/') => 'audio',
            in_array($this->extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'], true) => 'document',
            in_array($this->extension, ['zip', 'rar', '7z', 'gz'], true) => 'archive',
            default => 'other',
        };

        if ($this->is_image) {
            try {
                [$this->width, $this->height] = getimagesize($disk->path($this->path)) ?: [null, null];
            } catch (Throwable) {
                $this->width = $this->height = null;
            }
        }

        $this->last_scanned_at = now();
        $this->saveQuietly();
    }
}
