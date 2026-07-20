<?php

namespace App\Support;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\LandingPageSection;
use App\Models\LandingPageTranslation;
use App\Models\OrderItem;
use App\Models\ProductMedia;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\ProductVariantTranslation;
use App\Models\SitePageTranslation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuditLogger
{
    private const SENSITIVE_PATTERN = '/password|secret|token|credential|api[_-]?key|private[_-]?key|remember/i';

    public function model(Model $model, string $event): void
    {
        $changes = $event === 'updated' ? Arr::except($model->getChanges(), ['updated_at']) : [];

        if ($event === 'updated' && $changes === []) {
            return;
        }

        $oldValues = match ($event) {
            'updated' => Arr::only($model->getRawOriginal(), array_keys($changes)),
            'deleted' => Arr::except($model->getRawOriginal(), ['created_at', 'updated_at']),
            default => [],
        };
        $newValues = match ($event) {
            'created' => Arr::except($model->getAttributes(), ['created_at', 'updated_at']),
            'updated' => $changes,
            default => [],
        };

        $this->write([
            'account_id' => $this->accountId($model),
            'user_id' => auth()->id(),
            'event' => $event,
            'module' => $this->module($model),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'subject_label' => $this->subjectLabel($model),
            'description' => $this->description($model, $event),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
        ]);
    }

    public function authentication(User $user, string $event): void
    {
        $this->write([
            'account_id' => $user->account_id,
            'user_id' => $user->id,
            'event' => $event,
            'module' => 'المصادقة',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'subject_label' => $user->name ?: $user->email,
            'description' => $event === 'login' ? 'تم تسجيل الدخول إلى لوحة التحكم.' : 'تم تسجيل الخروج من لوحة التحكم.',
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    private function write(array $data): void
    {
        if (blank($data['account_id'] ?? null)) {
            return;
        }

        try {
            $request = app()->bound('request') ? request() : null;
            AuditLog::query()->create($data + [
                'ip_address' => $request?->ip(),
                'user_agent' => Str::limit((string) $request?->userAgent(), 1000, ''),
                'url' => $request?->fullUrl(),
                'request_method' => $request?->method(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function accountId(Model $model): ?int
    {
        if ($model instanceof Account) {
            return (int) $model->getKey();
        }

        if (filled($model->getAttribute('account_id'))) {
            return (int) $model->getAttribute('account_id');
        }

        return match (true) {
            $model instanceof LandingPageSection, $model instanceof LandingPageTranslation => $model->landingPage()->value('account_id'),
            $model instanceof ProductTranslation, $model instanceof ProductMedia, $model instanceof ProductVariant => $model->product()->value('account_id'),
            $model instanceof ProductVariantTranslation => $model->productVariant()->with('product')->first()?->product?->account_id,
            $model instanceof OrderItem => $model->order()->value('account_id'),
            $model instanceof SitePageTranslation => $model->sitePage()->value('account_id'),
            default => auth()->user()?->account_id,
        };
    }

    private function module(Model $model): string
    {
        return match (class_basename($model)) {
            'Account' => 'إعدادات النظام', 'User' => 'المستخدمون', 'Customer' => 'العملاء',
            'LandingPage', 'LandingPageTranslation', 'LandingPageSection' => 'صفحات الهبوط',
            'Product', 'ProductTranslation', 'ProductVariant', 'ProductVariantTranslation', 'ProductMedia' => 'المنتجات',
            'Order', 'OrderItem' => 'الطلبات', 'OrderStatus' => 'حالات الطلبات', 'Review' => 'التقييمات',
            'SitePage', 'SitePageTranslation' => 'صفحات الموقع', 'ContactMessage' => 'رسائل التواصل',
            'MediaAsset' => 'الوسائط', default => class_basename($model),
        };
    }

    private function subjectLabel(Model $model): string
    {
        foreach (['order_number', 'name', 'name_ar', 'title', 'sku', 'email', 'slug', 'original_name', 'label', 'path'] as $attribute) {
            if (filled($model->getAttribute($attribute))) {
                return Str::limit(strip_tags((string) $model->getAttribute($attribute)), 180, '…');
            }
        }

        return class_basename($model).' #'.($model->getKey() ?: 'جديد');
    }

    private function description(Model $model, string $event): string
    {
        $action = match ($event) {
            'created' => 'إنشاء', 'updated' => 'تحديث', 'deleted' => 'حذف', 'restored' => 'استعادة', default => $event,
        };

        return "{$action} سجل في قسم {$this->module($model)}.";
    }

    private function sanitize(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && preg_match(self::SENSITIVE_PATTERN, $key)) {
            return '[محجوب]';
        }

        if (is_array($value)) {
            return collect($value)->mapWithKeys(fn (mixed $item, string|int $itemKey): array => [
                $itemKey => $this->sanitize($item, (string) $itemKey),
            ])->all();
        }

        if (is_string($value)) {
            return Str::limit($value, 10000, '…');
        }

        return $value;
    }
}
