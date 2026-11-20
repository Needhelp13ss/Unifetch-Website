<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    public const TYPES = [
        'topup' => 'Top-up',
        'payment' => 'Order payment',
        'refund' => 'Refund',
        'earning' => 'Delivery earning',
        'remittance' => 'Cash remittance',
        'adjustment' => 'Admin adjustment',
    ];

    protected $fillable = ['user_id', 'order_id', 'type', 'amount', 'balance_after', 'description'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'balance_after' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
