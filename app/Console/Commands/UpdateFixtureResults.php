<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\LeagueTableService;
use Carbon\Carbon;

class UpdateFixtureResults extends Command
{
    protected $signature = 'fixtures:update-results';
    protected $description = 'Interactively update fixture results for matches that should be finished';

    protected $leagueTableService;

    public function __construct(LeagueTableService $leagueTableService)
    {
        parent::__construct();
        $this->leagueTableService = $leagueTableService;
    }

    public function handle()
    {
        $this->info('🏆 Fixture Results Update Tool');
        $this->info('Looking for fixtures that started more than 90 minutes ago...');
        $this->newLine();

        // Get fixtures that started more than 90 minutes ago and are not finished
        $cutoffTime = Carbon::now()->subMinutes(90);
        
        $fixtures = Fixture::with(['homeTeam', 'awayTeam', 'season'])
            ->where('match_datetime', '<', $cutoffTime)
            ->whereIn('status', ['scheduled', 'in_play'])
            ->orderBy('match_datetime', 'desc')
            ->get();

        if ($fixtures->isEmpty()) {
            $this->info('✅ No fixtures found that need updating.');
            return 0;
        }

        $this->info("Found {$fixtures->count()} fixture(s) that may need updating:");
        $this->newLine();

        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($fixtures as $fixture) {
            $this->displayFixtureInfo($fixture);
            
            // Ask if the game is finished
            if (!$this->confirm('Is this match finished?', true)) {
                $this->info('⏭️  Skipping this match.');
                $skippedCount++;
                $this->newLine();
                continue;
            }

            // Get scores
            $homeScore = $this->getScore($fixture->homeTeam->name, 'home');
            $awayScore = $this->getScore($fixture->awayTeam->name, 'away');

            // Confirm the result
            $result = "{$fixture->homeTeam->name} {$homeScore} - {$awayScore} {$fixture->awayTeam->name}";
            
            if (!$this->confirm("Confirm result: {$result}?", true)) {
                $this->warn('❌ Result not confirmed. Skipping this match.');
                $skippedCount++;
                $this->newLine();
                continue;
            }

            // Update the fixture
            $fixture->update([
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'status' => 'finished'
            ]);

            $this->info("✅ Updated: {$result}");
            $updatedCount++;

            // The league table will be automatically updated via the Fixture model event
            $this->newLine();
        }

        // Summary
        $this->info('📊 Update Summary:');
        $this->info("✅ Updated: {$updatedCount} fixture(s)");
        $this->info("⏭️  Skipped: {$skippedCount} fixture(s)");

        if ($updatedCount > 0) {
            $this->info('🔄 League table has been automatically updated!');
        }

        return 0;
    }

    private function displayFixtureInfo(Fixture $fixture)
    {
        $matchTime = $fixture->match_datetime->format('Y-m-d H:i');
        $minutesAgo = Carbon::now()->diffInMinutes($fixture->match_datetime);
        
        $this->info("🏟️  Match: {$fixture->homeTeam->name} vs {$fixture->awayTeam->name}");
        $this->info("📅 Scheduled: {$matchTime} ({$minutesAgo} minutes ago)");
        $this->info("📊 Status: {$fixture->status}");
        $this->info("🏆 Season: {$fixture->season->name}");
        $this->info("🗓️  Matchweek: {$fixture->matchweek}");
        
        if ($fixture->status === 'in_play' && $fixture->home_score !== null && $fixture->away_score !== null) {
            $this->info("⚽ Current Score: {$fixture->homeTeam->name} {$fixture->home_score} - {$fixture->away_score} {$fixture->awayTeam->name}");
        }
        
        $this->newLine();
    }

    private function getScore($teamName, $type)
    {
        while (true) {
            $score = $this->ask("⚽ Enter {$type} team ({$teamName}) score");
            
            if (is_numeric($score) && $score >= 0 && $score <= 20) {
                return (int) $score;
            }
            
            $this->error('Please enter a valid score (0-20)');
        }
    }
}
