<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = ['name', 'tagline', 'location', 'is_open', 'campus_location_id'];

    protected function casts(): array
    {
        return ['is_open' => 'boolean'];
    }

    public function place(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CampusLocation::class, 'campus_location_id');
    }

    public function owner(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class)->where('role', 'shop');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
