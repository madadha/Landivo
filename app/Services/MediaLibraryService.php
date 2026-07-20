<?php

namespace App\Services;

use App\Models\Account;
use App\Models\LandingPage;
use App\Models\LandingPageSection;
use App\Models\MediaAsset;
use App\Models\OrderAttachment;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\SitePageTranslation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryService
{
    public function synchronizeAccount(int $accountId, bool $force = false): array
    {
        $cacheKey = "media-library-sync:{$accountId}";
        if (! $force && Cache::has($cacheKey)) {
            return ['skipped' => true];
        }

        $references = $this->buildReferenceIndex($accountId);
        $disk = Storage::disk('public');
        $paths = array_merge(array_keys($references), $disk->allFiles("media-library/account-{$accountId}"));
        if (Account::query()->count() === 1) {
            $paths = array_merge($paths, $disk->allFiles());
        }

        $paths = collect($paths)->map(fn ($path) => $this->normalizePath((string) $path))->filter()->reject(fn (string $path) => str_starts_with($path, '.'))->unique()->values();
        $created = 0;

        foreach ($paths as $path) {
            $asset = MediaAsset::query()->firstOrNew(['account_id' => $accountId, 'disk' => 'public', 'path' => $path]);
            $created += $asset->exists ? 0 : 1;
            $asset->usage_locations = $references[$path] ?? [];
            $asset->usage_count = count($asset->usage_locations);
            $asset->save();
            $asset->refreshFileMetadata();
        }

        MediaAsset::query()->where('account_id', $accountId)->get()->each(function (MediaAsset $asset) use ($references): void {
            $asset->usage_locations = $references[$asset->path] ?? [];
            $asset->usage_count = count($asset->usage_locations);
            $asset->saveQuietly();
            $asset->refreshFileMetadata();
        });

        Cache::put($cacheKey, true, now()->addMinutes(2));

        return [
            'created' => $created,
            'total' => MediaAsset::query()->where('account_id', $accountId)->count(),
            'used' => MediaAsset::query()->where('account_id', $accountId)->where('usage_count', '>', 0)->count(),
            'unused' => MediaAsset::query()->where('account_id', $accountId)->where('usage_count', 0)->count(),
        ];
    }

    public function deleteUnused(MediaAsset $asset): bool
    {
        if ($asset->usage_count > 0) {
            return false;
        }
        Storage::disk($asset->disk)->delete($asset->path);
        $asset->delete();
        Cache::forget("media-library-sync:{$asset->account_id}");

        return true;
    }

    public function cleanUnused(int $accountId): array
    {
        $this->synchronizeAccount($accountId, true);
        $assets = MediaAsset::query()->where('account_id', $accountId)->where('usage_count', 0)->get();
        $bytes = $assets->sum('size');
        $count = 0;
        foreach ($assets as $asset) {
            $count += $this->deleteUnused($asset) ? 1 : 0;
        }

        return ['count' => $count, 'bytes' => $bytes];
    }

    private function buildReferenceIndex(int $accountId): array
    {
        $index = [];
        $add = function (Model $model, string $label, array $fields) use (&$index): void {
            foreach ($fields as $field) {
                $this->extractPaths(data_get($model, $field), function (string $path) use (&$index, $model, $label, $field): void {
                    $index[$path][] = ['type' => class_basename($model), 'id' => $model->getKey(), 'label' => $label, 'field' => $field];
                });
            }
        };

        Account::query()->whereKey($accountId)->get()->each(fn (Account $m) => $add($m, 'إعدادات النظام', ['logo_path', 'favicon_path', 'settings']));
        User::query()->where('account_id', $accountId)->get()->each(fn (User $m) => $add($m, 'المستخدم: '.$m->name, ['avatar_url']));
        Product::query()->where('account_id', $accountId)->get()->each(fn (Product $m) => $add($m, 'المنتج: '.$m->sku, ['primary_image_path', 'metadata']));
        ProductMedia::query()->whereHas('product', fn ($q) => $q->where('account_id', $accountId))->get()->each(fn (ProductMedia $m) => $add($m, 'وسائط المنتج #'.$m->product_id, ['file_path']));
        ProductVariant::query()->whereHas('product', fn ($q) => $q->where('account_id', $accountId))->get()->each(fn (ProductVariant $m) => $add($m, 'متغير المنتج: '.$m->sku, ['image_path']));
        LandingPage::query()->where('account_id', $accountId)->get()->each(fn (LandingPage $m) => $add($m, 'صفحة الهبوط: /'.$m->slug, ['settings']));
        LandingPageSection::query()->whereHas('landingPage', fn ($q) => $q->where('account_id', $accountId))->get()->each(fn (LandingPageSection $m) => $add($m, 'قسم في صفحة هبوط #'.$m->landing_page_id, ['settings']));
        Review::query()->where('account_id', $accountId)->get()->each(fn (Review $m) => $add($m, 'تقييم الزائر: '.$m->name, ['photo_path']));
        SitePageTranslation::query()->whereHas('sitePage', fn ($q) => $q->where('account_id', $accountId))->get()->each(fn (SitePageTranslation $m) => $add($m, 'صفحة الموقع: '.$m->title, ['hero_image', 'blocks', 'content']));
        OrderAttachment::query()->whereHas('order', fn ($q) => $q->where('account_id', $accountId))->get()->each(fn (OrderAttachment $m) => $add($m, 'مرفق الطلب #'.$m->order_id, ['path']));

        return collect($index)->map(fn (array $items) => collect($items)->unique(fn ($item) => implode(':', [$item['type'], $item['id'], $item['field']]))->values()->all())->all();
    }

    private function extractPaths(mixed $value, callable $callback): void
    {
        if (is_array($value)) {
            foreach ($value as $child) {
                $this->extractPaths($child, $callback);
            }

            return;
        }
        if (! is_string($value) || blank($value)) {
            return;
        }

        if (preg_match_all('~(?:https?://[^\\s"\'<>]+)?/storage/([^\\s"\'<>?#]+)~iu', $value, $matches)) {
            foreach ($matches[1] as $match) {
                if ($path = $this->normalizePath(rawurldecode($match))) {
                    $callback($path);
                }
            }
        }
        if (! Str::contains($value, ['<', '>', "\n", '://']) && preg_match('/\.[a-z0-9]{2,8}$/i', parse_url($value, PHP_URL_PATH) ?: '')) {
            if ($path = $this->normalizePath($value)) {
                $callback($path);
            }
        }
    }

    private function normalizePath(string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path));
        $path = preg_replace('~^https?://[^/]+/storage/~i', '', $path) ?? $path;
        $path = preg_replace('~^/?storage/~i', '', $path) ?? $path;
        $path = ltrim(strtok($path, '?#') ?: '', '/');

        return $path !== '' && ! str_contains($path, '..') ? $path : null;
    }
}
