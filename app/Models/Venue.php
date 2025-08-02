<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_fa',
        'city',
        'city_fa',
        'capacity',
        'address',
        'address_fa',
        'photo_url',
        'slug',
        'api_id',
        'is_active',
    ];

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }
}