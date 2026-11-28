<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OrderPhoto extends Model
{
    public const KINDS = [
        'shop_handover' => 'Shop: runner collected the order',
        'runner_pickup' => 'Runner: picked up the order',
        'runner_delivery' => 'Runner: delivered the order',
        'customer_received' => 'Customer: received the order',
    ];

    protected $fillable = ['order_id', 'user_id', 'kind', 'path'];

    /** Photo rules shared by every upload form. */
    public const RULES = ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'];

    /** One photo per kind and order: a new upload replaces the old one. */
    public static function saveFor(Order $order, User $user, string $kind, UploadedFile $file): self
    {
        $existing = self::where('order_id', $order->id)->where('kind', $kind)->first();
        if ($existing) {
            Storage::delete($existing->path);
        }

        return self::updateOrCreate(
            ['order_id' => $order->id, 'kind' => $kind],
            ['user_id' => $user->id, 'path' => $file->store('order-photos/' . $order->id)]
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLabelAttribute(): string
    {
        return self::KINDS[$this->kind] ?? $this->kind;
    }
}
