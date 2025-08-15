<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Season;
use App\Models\Team;
use App\Models\LeagueTable;

class LeagueTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create current season
        $season = Season::firstOrCreate([
            'name' => '2024-25',
            'is_active' => true
        ]);

        // Premier League teams - all start with 0 points and stats at beginning of season
        $teamNames = [
            'Manchester City',
            'Arsenal', 
            'Liverpool',
            'Chelsea',
            'Manchester United',
            'Tottenham Hotspur',
            'Newcastle United',
            'West Ham United',
            'Brighton & Hove Albion',
            'Aston Villa',
            'Fulham',
            'Brentford',
            'Crystal Palace',
            'Everton',
            'Wolverhampton Wanderers',
            'Nottingham Forest',
            'Bournemouth',
            'Leeds United',
            'Burnley',
            'Sunderland',
        ];

        foreach ($teamNames as $index => $teamName) {
            // Create or get team
            $team = Team::firstOrCreate(['name' => $teamName]);
            
            // Initialize league table entry with 0 stats (beginning of season)
            LeagueTable::updateOrCreate(
                [
                    'season_id' => $season->id,
                    'team_id' => $team->id,
                ],
                [
                    'position' => $index + 1, // Initial alphabetical/default position
                    'played' => 0,
                    'won' => 0,
                    'drawn' => 0,
                    'lost' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                    'goal_difference' => 0,
                    'points' => 0,
                    'form' => '', // No form at beginning
                ]
            );
        }

        echo "League table initialized for season start: {$season->name}\n";
        echo "All teams start with 0 points and stats. Table will be updated as fixtures are completed.\n";
    }
}
