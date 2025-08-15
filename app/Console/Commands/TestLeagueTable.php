<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LeagueTableService;
use App\Models\Season;

class TestLeagueTable extends Command
{
    protected $signature = 'test:league-table';
    protected $description = 'Test the league table functionality';

    public function handle()
    {
        $season = Season::where('is_active', true)->first();
        
        if (!$season) {
            $this->error('No active season found');
            return 1;
        }

        $this->info("Testing league table for season: {$season->name}");
        
        $service = app(LeagueTableService::class);
        $table = $service->getLiveTable($season->id);
        
        $this->info("Found {$table->count()} teams in the table");
        
        foreach ($table->take(5) as $row) {
            $this->line("{$row['live_position']}. {$row['team']['name']} - {$row['points']} pts");
        }
        
        $this->info('League table test completed successfully!');
        return 0;
    }
}
