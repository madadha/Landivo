<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['account_id', 'landing_page_id', 'customer_id', 'order_status_id', 'order_number', 'subtotal', 'total', 'currency', 'source', 'utm_parameters', 'ip_address', 'user_agent', 'notes', 'form_data'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'total' => 'decimal:2', 'utm_parameters' => 'array', 'form_data' => 'array'];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return BelongsTo<LandingPage, $this> */
    public function landingPage(): BelongsTo { return $this->belongsTo(LandingPage::class); }

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
    public function activities(): HasMany { return $this->hasMany(OrderActivity::class)->latest(); }

    /** @return HasMany<OrderAttachment, $this> */
    public function attachments(): HasMany { return $this->hasMany(OrderAttachment::class)->latest(); }
}
