<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    public const CATEGORIES = [
        'meals' => 'Meals',
        'groceries' => 'Groceries',
        'drinks' => 'Drinks',
        'medicine' => 'Medicine',
        'essentials' => 'Essentials',
    ];

    protected $fillable = [
        'vendor_id', 'name', 'description', 'category', 'price',
        'prep_time', 'emoji', 'bg_color', 'image', 'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
