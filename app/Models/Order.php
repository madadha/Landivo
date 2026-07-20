<?php

namespace App\Models;

use App\Support\OrderInventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class Order extends Model
{
    protected $fillable = ['account_id', 'landing_page_id', 'customer_id', 'order_status_id', 'order_number', 'subtotal', 'total', 'currency', 'source', 'utm_parameters', 'ip_address', 'user_agent', 'notes', 'archived_at', 'follow_up_at', 'follow_up_note', 'follow_up_completed_at', 'inventory_deducted_at', 'form_data'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'utm_parameters' => 'array',
            'form_data' => 'array',
            'follow_up_at' => 'datetime',
            'follow_up_completed_at' => 'datetime',
            'inventory_deducted_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Order $order): void {
            $order->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'system',
                'body' => 'تم إنشاء الطلب وإضافته إلى النظام.',
                'metadata' => ['event' => 'created'],
            ]);
        });

        static::updated(function (Order $order): void {
            $changes = Arr::except($order->getChanges(), ['updated_at']);

            if ($changes === []) {
                return;
            }

            $labels = [
                'order_status_id' => 'حالة الطلب',
                'customer_id' => 'العميل',
                'total' => 'الإجمالي',
                'currency' => 'العملة',
                'source' => 'المصدر',
                'notes' => 'الملاحظات',
                'form_data' => 'بيانات النموذج أو العرض المختار',
                'follow_up_at' => 'موعد المتابعة',
                'follow_up_note' => 'سبب التأجيل أو ملاحظة المتابعة',
                'follow_up_completed_at' => 'حالة التذكير',
            ];

            $summaries = collect($changes)
                ->map(function (mixed $value, string $field) use ($order, $labels): string {
                    $label = $labels[$field] ?? $field;

                    if ($field === 'order_status_id') {
                        $old = OrderStatus::find($order->getOriginal($field))?->name_ar ?: 'غير محدد';
                        $new = $order->status?->name_ar ?: 'غير محدد';

                        return "{$label}: {$old} ← {$new}";
                    }

                    if ($field === 'follow_up_at') {
                        return filled($value)
                            ? "تم تحديد {$label} في ".Carbon::parse($value)->format('Y-m-d H:i')
                            : 'تم إلغاء موعد المتابعة.';
                    }

                    if ($field === 'follow_up_completed_at') {
                        return filled($value) ? 'تم إنجاز التذكير وإغلاق المتابعة.' : 'تمت إعادة فتح التذكير للمتابعة.';
                    }

                    return "تم تحديث {$label}.";
                })
                ->values()
                ->all();

            $order->activities()->create([
                'user_id' => auth()->id(),
                'type' => array_key_exists('follow_up_at', $changes) ? 'follow_up' : 'update',
                'body' => implode(' ', $summaries),
                'metadata' => [
                    'event' => 'updated',
                    'changes' => $changes,
                ],
            ]);

            if (array_key_exists('order_status_id', $changes)) {
                app(OrderInventoryService::class)->syncForStatusChange(
                    $order,
                    OrderStatus::find($order->getOriginal('order_status_id')),
                    OrderStatus::find($order->order_status_id),
                );
            }
        });
    }

    public function hasPendingFollowUp(): bool
    {
        return filled($this->follow_up_at) && blank($this->follow_up_completed_at);
    }

    public function isFollowUpDue(): bool
    {
        return $this->hasPendingFollowUp() && $this->follow_up_at->isPast();
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return BelongsTo<LandingPage, $this> */
    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<OrderStatus, $this> */
    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<OrderActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(OrderActivity::class)->latest();
    }

    /** @return HasMany<OrderAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(OrderAttachment::class)->latest();
    }

    /** @return HasOne<Review, $this> */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
