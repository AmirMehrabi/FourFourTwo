<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Season;
use App\Models\Team;
use App\Models\Fixture;
use App\Services\LeagueTableService;

class CreateSampleFixtures extends Command
{
    protected $signature = 'create:sample-fixtures';
    protected $description = 'Create sample fixtures for testing';

    public function handle()
    {
        $season = Season::where('is_active', true)->first();
        
        if (!$season) {
            $this->error('No active season found');
            return 1;
        }

        $teams = Team::take(4)->get(); // Get first 4 teams
        
        if ($teams->count() < 4) {
            $this->error('Need at least 4 teams to create sample fixtures');
            return 1;
        }

        // Create a sample fixture between first two teams (finished)
        $fixture1 = Fixture::create([
            'season_id' => $season->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'match_datetime' => now()->subHours(2),
            'matchweek' => 1,
            'status' => 'finished',
            'home_score' => 2,
            'away_score' => 1,
        ]);

        // Create a live fixture between teams 3 and 4
        $fixture2 = Fixture::create([
            'season_id' => $season->id,
            'home_team_id' => $teams[2]->id,
            'away_team_id' => $teams[3]->id,
            'match_datetime' => now()->subMinutes(45),
            'matchweek' => 1,
            'status' => 'in_play',
            'home_score' => 1,
            'away_score' => 0,
        ]);

        $this->info('Sample fixtures created:');
        $this->info("1. {$teams[0]->name} 2-1 {$teams[1]->name} (Finished)");
        $this->info("2. {$teams[2]->name} 1-0 {$teams[3]->name} (Live)");

        // Update league table
        $service = app(LeagueTableService::class);
        $service->updateTableForSeason($season->id);
        
        $this->info('League table updated!');
        return 0;
    }
}
