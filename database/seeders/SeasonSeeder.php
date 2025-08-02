<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Season;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        Season::create([
            'name' => '2025/26',
            'start_date' => '2025-08-16',
            'end_date' => '2026-05-24',
            'is_active' => true,
        ]);
    }
}