<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'vendor_name', 'name', 'emoji', 'price', 'quantity',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getLineTotalAttribute(): float
    {
        return (float) $this->price * $this->quantity;
    }
}
