<?php

namespace App\Support;

use App\Models\Account;
use Illuminate\Support\Facades\Storage;

final class InvoiceLogo
{
    public static function dataUri(?Account $account): ?string
    {
        $path = trim((string) $account?->logo_path);
        $disk = Storage::disk('public');

        if ($path === '' || ! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);
        if ($contents === '') {
            return null;
        }

        $mime = $disk->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
