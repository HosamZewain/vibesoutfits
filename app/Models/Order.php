<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'subtotal' => 'integer', 'shipping_fee' => 'integer', 'total' => 'integer',
        'deposit_due' => 'integer', 'paid_amount' => 'integer', 'refunded_amount' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class);
    }

    public function allApproved(): bool
    {
        return $this->items->every(fn (OrderItem $item) => $item->design_mode === 'ready' || ($item->design_status === 'approved' && $item->approved_at !== null));
    }

    public function balance(): int
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function label(): string
    {
        if ($this->status === 'new') {
            if ($this->paid_amount < $this->deposit_due) {
                return 'في انتظار التحويل';
            }
            if (! $this->allApproved()) {
                return 'التصميم تحت المراجعة';
            }
        }

        return config('vibe.statuses.'.$this->status);
    }

    public function addEvent(string $message, bool $visible = true): void
    {
        $this->events()->create(compact('message', 'visible'));
    }
}
