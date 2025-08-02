<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        Team::query()->delete(); // Clear the table

        $teams = [
            'Arsenal', 'Aston Villa', 'Bournemouth', 'Brentford', 'Brighton & Hove Albion',
            'Burnley', 'Chelsea', 'Crystal Palace', 'Everton', 'Fulham', 'Leeds United',
            'Liverpool', 'Manchester City', 'Manchester United', 'Newcastle United',
            'Nottingham Forest', 'Sunderland', 'Tottenham Hotspur', 'West Ham United',
            'Wolverhampton Wanderers'
        ];

        foreach ($teams as $teamName) {
            Team::create([
                'name' => $teamName,
                'slug' => Str::slug($teamName),
            ]);
        }
    }
}