<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusLocation extends Model
{
    protected $fillable = ['name', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float'];
    }
}
