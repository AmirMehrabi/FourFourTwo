<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\ActivityFeed;
use App\Models\LeagueTable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupFixtures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:cleanup {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactive command to clean up fixtures and all related data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Scanning for fixtures...');
        
        // Get all fixtures with their related data
        $fixtures = Fixture::with(['homeTeam', 'awayTeam', 'venue'])
            ->orderBy('match_datetime', 'desc')
            ->get();

        if ($fixtures->isEmpty()) {
            $this->info('✅ No fixtures found in the database.');
            return 0;
        }

        $this->info("📊 Found {$fixtures->count()} fixtures:");
        $this->newLine();

        // Display fixtures in a table
        $headers = ['ID', 'Match', 'Date', 'Status', 'Score', 'Predictions', 'Comments', 'Activities'];
        $rows = [];

        foreach ($fixtures as $fixture) {
            $predictionsCount = Prediction::where('fixture_id', $fixture->id)->count();
            $commentsCount = Comment::where('fixture_id', $fixture->id)->count();
            $activitiesCount = ActivityFeed::where('activity_type', 'prediction')
                ->whereJsonContains('data->fixture_id', $fixture->id)
                ->count();

            $score = $fixture->home_score !== null && $fixture->away_score !== null 
                ? "{$fixture->home_score}-{$fixture->away_score}" 
                : 'N/A';

            $rows[] = [
                $fixture->id,
                "{$fixture->homeTeam->name} vs {$fixture->awayTeam->name}",
                $fixture->match_datetime->format('Y-m-d H:i'),
                $fixture->status,
                $score,
                $predictionsCount,
                $commentsCount,
                $activitiesCount
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        // Interactive selection
        $this->info('🗑️  Select fixtures to delete:');
        $this->info('   • Enter fixture IDs separated by commas (e.g., 1,3,5)');
        $this->info('   • Enter "all" to delete all fixtures');
        $this->info('   • Enter "q" to quit without deleting');
        $this->newLine();

        $input = $this->ask('Which fixtures do you want to delete?');

        if (strtolower($input) === 'q') {
            $this->info('❌ Operation cancelled.');
            return 0;
        }

        $fixtureIds = [];
        
        if (strtolower($input) === 'all') {
            $fixtureIds = $fixtures->pluck('id')->toArray();
            $this->warn("⚠️  You are about to delete ALL {$fixtures->count()} fixtures and all related data!");
        } else {
            $fixtureIds = array_map('intval', array_filter(explode(',', $input)));
            $fixtureIds = array_unique($fixtureIds);
            
            // Validate fixture IDs
            $validIds = $fixtures->pluck('id')->toArray();
            $invalidIds = array_diff($fixtureIds, $validIds);
            
            if (!empty($invalidIds)) {
                $this->error('❌ Invalid fixture IDs: ' . implode(', ', $invalidIds));
                return 1;
            }
        }

        if (empty($fixtureIds)) {
            $this->error('❌ No valid fixture IDs provided.');
            return 1;
        }

        // Show what will be deleted
        $selectedFixtures = $fixtures->whereIn('id', $fixtureIds);
        $this->newLine();
        $this->warn('⚠️  The following fixtures will be deleted:');
        
        foreach ($selectedFixtures as $fixture) {
            $this->line("   • ID {$fixture->id}: {$fixture->homeTeam->name} vs {$fixture->awayTeam->name}");
        }

        $this->newLine();

        // Count related data
        $totalPredictions = Prediction::whereIn('fixture_id', $fixtureIds)->count();
        $totalComments = Comment::whereIn('fixture_id', $fixtureIds)->count();
        $totalCommentReactions = CommentReaction::whereHas('comment', function($query) use ($fixtureIds) {
            $query->whereIn('fixture_id', $fixtureIds);
        })->count();
        $totalActivities = ActivityFeed::where('activity_type', 'prediction')
            ->where(function($query) use ($fixtureIds) {
                foreach ($fixtureIds as $fixtureId) {
                    $query->orWhereJsonContains('data->fixture_id', $fixtureId);
                }
            })->count();

        $this->warn("📊 Related data to be deleted:");
        $this->line("   • {$totalPredictions} predictions");
        $this->line("   • {$totalComments} comments");
        $this->line("   • {$totalCommentReactions} comment reactions");
        $this->line("   • {$totalActivities} activity feed entries");
        $this->newLine();

        // Confirmation
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to proceed with the deletion?')) {
                $this->info('❌ Operation cancelled.');
                return 0;
            }
        }

        // Perform deletion in transaction
        $this->info('🗑️  Starting deletion process...');
        
        try {
            DB::transaction(function () use ($fixtureIds, $totalPredictions, $totalComments, $totalCommentReactions, $totalActivities) {
                // Delete comment reactions first (foreign key constraint)
                $this->line('   • Deleting comment reactions...');
                CommentReaction::whereHas('comment', function($query) use ($fixtureIds) {
                    $query->whereIn('fixture_id', $fixtureIds);
                })->delete();

                // Delete comments
                $this->line('   • Deleting comments...');
                Comment::whereIn('fixture_id', $fixtureIds)->delete();

                // Delete predictions
                $this->line('   • Deleting predictions...');
                Prediction::whereIn('fixture_id', $fixtureIds)->delete();

                // Delete activity feed entries
                $this->line('   • Deleting activity feed entries...');
                ActivityFeed::where('activity_type', 'prediction')
                    ->where(function($query) use ($fixtureIds) {
                        foreach ($fixtureIds as $fixtureId) {
                            $query->orWhereJsonContains('data->fixture_id', $fixtureId);
                        }
                    })->delete();

                // Delete fixtures
                $this->line('   • Deleting fixtures...');
                Fixture::whereIn('id', $fixtureIds)->delete();

                // Update league table (recalculate for all teams)
                $this->line('   • Updating league table...');
                $this->updateLeagueTable();
            });

            $this->newLine();
            $this->info('✅ Successfully deleted:');
            $this->line("   • " . count($fixtureIds) . " fixtures");
            $this->line("   • {$totalPredictions} predictions");
            $this->line("   • {$totalComments} comments");
            $this->line("   • {$totalCommentReactions} comment reactions");
            $this->line("   • {$totalActivities} activity feed entries");
            $this->line("   • Updated league table");
            
            $this->newLine();
            $this->info('🎉 Cleanup completed successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Error during deletion: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Update league table after fixture deletion
     */
    private function updateLeagueTable()
    {
        try {
            $currentSeason = \App\Models\Season::where('is_active', true)->first();
            if ($currentSeason) {
                $leagueTableService = new \App\Services\LeagueTableService();
                $leagueTableService->updateTableForSeason($currentSeason->id);
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Warning: Could not update league table: ' . $e->getMessage());
        }
    }
}