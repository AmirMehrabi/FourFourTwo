<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\LeagueTableService;
use Carbon\Carbon;

class UpdateFixtureResults extends Command
{
    protected $signature = 'fixtures:update-results 
        {--minutes=90 : Minutes ago to look for finalize candidates} 
        {--live : Only handle starting & in-play matches (update live score)} 
        {--finalize : Only finalize matches likely finished} 
        {--fixture= : Target a single fixture ID} 
        {--auto : Non-interactive; assume defaults} 
        {--debug : Show debug information}';
    protected $description = 'Update fixture scores: start, live update, and finalize matches';

    protected $leagueTableService;
    protected \App\Services\FixtureUpdateService $fixtureUpdateService;

    public function __construct(LeagueTableService $leagueTableService, \App\Services\FixtureUpdateService $fixtureUpdateService)
    {
        parent::__construct();
        $this->leagueTableService = $leagueTableService;
        $this->fixtureUpdateService = $fixtureUpdateService;
    }

    public function handle()
    {
        $minutes = (int) $this->option('minutes') ?: 90;
        $debug = $this->option('debug');
        $onlyLive = $this->option('live');
        $onlyFinalize = $this->option('finalize');
        $fixtureId = $this->option('fixture');
        $auto = $this->option('auto');

        if ($onlyLive && $onlyFinalize) {
            $this->error('Use only one of --live or --finalize, not both.');
            return 1;
        }

        $this->info('🏆 Fixture Lifecycle Update Tool');
        $this->line('Modes: start -> live scoring (in_play) -> finalize (finished)');
        $this->newLine();

        // Pre-load candidates
        $cutoffTime = Carbon::now()->subMinutes($minutes);

        // Show debug info
        if ($debug) {
            $this->info("Current time: " . Carbon::now()->format('Y-m-d H:i:s'));
            $this->info("Finalize cutoff (>{$minutes} min ago): " . $cutoffTime->format('Y-m-d H:i:s'));
            $this->newLine();
        }

        // Start any due fixtures (scheduled whose time has passed) automatically before processing
        $startedCount = $this->fixtureUpdateService->startDueFixtures($fixtureId);
        if ($startedCount > 0) {
            $this->info("⏱️  Auto-started {$startedCount} scheduled fixture(s) now in play.");
        }

        $liveCandidates = $onlyFinalize ? collect([]) : $this->fixtureUpdateService->getLiveCandidates($fixtureId);
        $finalizeCandidates = $onlyLive ? collect([]) : $this->fixtureUpdateService->getFinalizeCandidates($minutes, $fixtureId);

        if ($debug) {
            $this->line('Debug: Live candidates: ' . $liveCandidates->count());
            $this->line('Debug: Finalize candidates: ' . $finalizeCandidates->count());
            $this->newLine();
        }

        if ($liveCandidates->isEmpty() && $finalizeCandidates->isEmpty()) {
            $this->info('✅ No fixtures need attention right now.');
            return 0;
        }

        $updatedLive = 0;
        $finalized = 0;
        $skipped = 0;

        // Process live updates
        if ($liveCandidates->isNotEmpty()) {
            $this->info('🎬 Live / Starting Fixtures:');
            foreach ($liveCandidates as $fixture) {
                $this->displayFixtureInfo($fixture);
                if ($onlyFinalize) {
                    continue; // safety
                }

                if ($auto) {
                    // In auto mode we don't change scores unless both null -> skip
                    if ($fixture->home_score === null && $fixture->away_score === null) {
                        $skipped++; continue;
                    }
                } else {
                    $action = $this->choice('Action', ['Live Update', 'Finalize', 'Skip', 'Quit'], 0);
                    if ($action === 'Quit') { break; }
                    if ($action === 'Skip') { $skipped++; $this->newLine(); continue; }
                    if ($action === 'Finalize') {
                        // fallthrough to finalize section later by adding to finalize list if not already
                        if (!$finalizeCandidates->contains('id', $fixture->id)) {
                            $finalizeCandidates->push($fixture);
                        }
                        continue; // skip live update for now
                    }
                }

                // Live Update path
                $homeScore = $this->askScoreNullable($fixture->homeTeam->name, 'home', $fixture->home_score, $auto);
                $awayScore = $this->askScoreNullable($fixture->awayTeam->name, 'away', $fixture->away_score, $auto);

                if ($homeScore === $fixture->home_score && $awayScore === $fixture->away_score) {
                    $this->line('No score change.');
                    $skipped++; $this->newLine(); continue;
                }

                $this->fixtureUpdateService->updateLiveScore($fixture, $homeScore, $awayScore);
                $this->info("✅ Live score updated: {$fixture->homeTeam->name} {$homeScore} - {$awayScore} {$fixture->awayTeam->name}");
                $updatedLive++;
                $this->newLine();
            }
        }

        // Process finalizations
        if ($finalizeCandidates->isNotEmpty()) {
            $this->info('🏁 Finalization Candidates:');
            foreach ($finalizeCandidates as $fixture) {
                // Skip if finished already
                if ($fixture->status === 'finished') { continue; }
                $this->displayFixtureInfo($fixture);
                if ($onlyLive) { continue; }
                if (!$auto) {
                    if (!$this->confirm('Finalize this match?', true)) { $skipped++; $this->newLine(); continue; }
                }

                $homeScore = $this->askScoreNullable($fixture->homeTeam->name, 'home', $fixture->home_score, $auto, false);
                $awayScore = $this->askScoreNullable($fixture->awayTeam->name, 'away', $fixture->away_score, $auto, false);

                $result = "{$fixture->homeTeam->name} {$homeScore} - {$awayScore} {$fixture->awayTeam->name}";
                if (!$auto && !$this->confirm("Confirm final result: {$result}?", true)) { $skipped++; $this->newLine(); continue; }

                $this->fixtureUpdateService->finalizeFixture($fixture, $homeScore, $awayScore);
                $this->info("🏁 Finalized: {$result}");
                $finalized++;
                $this->newLine();
            }
        }

        // Summary
        $this->info('📊 Summary:');
        $this->info("🔄 Live updates: {$updatedLive}");
        $this->info("🏁 Finalized: {$finalized}");
        $this->info("⏭️  Skipped: {$skipped}");
        if (($updatedLive + $finalized) > 0) {
            $this->info('🔄 League table updated via model events.');
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
            if (is_numeric($score) && $score >= 0 && $score <= 20) { return (int)$score; }
            $this->error('Please enter a valid score (0-20)');
        }
    }

    private function askScoreNullable($teamName, $type, $current, $auto = false, $allowNull = true)
    {
        if ($auto) {
            // In auto mode keep existing
            return $current;
        }
        while (true) {
            $prompt = "⚽ Enter {$type} team ({$teamName}) score" . ($current !== null ? " [current: {$current}]" : '') . ($allowNull ? ' (blank=leave unchanged)' : '');
            $value = $this->ask($prompt);
            if ($value === null || $value === '') {
                if ($allowNull) { return $current; }
                $this->error('Score required'); continue;            
            }
            if (is_numeric($value) && $value >= 0 && $value <= 20) { return (int)$value; }
            $this->error('Please enter a valid score (0-20)');
        }
    }
}
