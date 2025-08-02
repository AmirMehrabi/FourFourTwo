<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'name_fa',
        'short_name',
        'short_name_fa',
        'logo_url',
        'slug',
        'primary_color',
        'secondary_color',
        'founded_year',
        'stadium_name',
        'stadium_name_fa',
        'stadium_capacity',
        'city',
        'city_fa',
        'manager',
        'manager_fa',
        'website_url',
        'api_id',
        'is_active',
    ];
}
