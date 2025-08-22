<?php

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        $start = Carbon::now()->startOfYear();
        $end = (clone $start)->addMonths(9);
        return [
            'name' => 'Season '.Str::random(5),
            'start_date' => $start,
            'end_date' => $end,
            'is_active' => true,
        ];
    }
}
