<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\LeagueTableService;
use Carbon\Carbon;

class UpdateFixtureResults extends Command
{
    protected $signature = 'fixtures:update-results {--minutes=90 : Minutes ago to look for fixtures} {--debug : Show debug information}';
    protected $description = 'Interactively update fixture results for matches that should be finished';

    protected $leagueTableService;

    public function __construct(LeagueTableService $leagueTableService)
    {
        parent::__construct();
        $this->leagueTableService = $leagueTableService;
    }

    public function handle()
    {
        $minutes = (int) $this->option('minutes') ?: 90;
        $debug = $this->option('debug');
        
        $this->info('🏆 Fixture Results Update Tool');
        $this->info("Looking for fixtures that started more than {$minutes} minutes ago...");
        
        // Get fixtures that started more than X minutes ago and are not finished
        $cutoffTime = Carbon::now()->subMinutes($minutes);
        
        if ($debug) {
            $this->info("Current time: " . Carbon::now()->format('Y-m-d H:i:s'));
            $this->info("Cutoff time ({$minutes} min ago): " . $cutoffTime->format('Y-m-d H:i:s'));
            $this->newLine();
            
            // Debug: Show all fixtures regardless of time first
            $allFixtures = Fixture::with(['homeTeam', 'awayTeam', 'season'])
                ->whereIn('status', ['scheduled', 'in_play'])
                ->orderBy('match_datetime', 'desc')
                ->get();
                
            $this->info("Debug: All fixtures with status 'scheduled' or 'in_play':");
            foreach ($allFixtures as $fixture) {
                $matchTime = Carbon::parse($fixture->match_datetime);
                $isOld = $matchTime->lt($cutoffTime);
                $this->info("- {$fixture->homeTeam->name} vs {$fixture->awayTeam->name} at {$fixture->match_datetime} (Should include: " . ($isOld ? 'YES' : 'NO') . ")");
            }
            $this->newLine();
        }
        
        $fixtures = Fixture::with(['homeTeam', 'awayTeam', 'season'])
            ->where('match_datetime', '<', $cutoffTime)
            ->whereIn('status', ['scheduled', 'in_play'])
            ->orderBy('match_datetime', 'desc')
            ->get();

        if ($fixtures->isEmpty()) {
            $this->info('✅ No fixtures found that need updating.');
            if (!$debug) {
                $this->info('💡 Try running with --debug to see more information.');
                $this->info('💡 Or try with fewer minutes: --minutes=30');
            }
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
