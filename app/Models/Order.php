<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** Flat delivery fee (pesos) charged once per order. Change it here. */
    public const DELIVERY_FEE = 20;

    public const STATUSES = [
        'pending' => 'Pending',
        'preparing' => 'Preparing',
        'on_the_way' => 'On the way',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_METHODS = [
        'cod' => 'Cash on Delivery',
        'ewallet' => 'E-wallet (GCash / Maya)',
    ];

    protected $fillable = [
        'user_id', 'order_number', 'status', 'subtotal', 'delivery_fee',
        'total', 'payment_method', 'delivery_location', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function canBeCancelled(): bool
    {
        return $this->status === 'pending';
    }
}
