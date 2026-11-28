<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMessage extends Model
{
    protected $fillable = ['order_id', 'user_id', 'body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
