<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\Prediction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculatePredictionPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates points for predictions on finished fixtures';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting points calculation...');

        // Find all finished fixtures where points have not yet been calculated
        $fixturesToProcess = Fixture::where('status', 'finished')
            ->where('points_calculated', false)
            ->whereNotNull('home_score') // Ensure scores are set
            ->whereNotNull('away_score')
            ->get();

        if ($fixturesToProcess->isEmpty()) {
            $this->info('No new fixtures to process.');
            return;
        }

        foreach ($fixturesToProcess as $fixture) {
            $this->line("Processing fixture: {$fixture->homeTeam->name} vs {$fixture->awayTeam->name}");

            // Get all predictions for this fixture
            $predictions = $fixture->predictions;

            foreach ($predictions as $prediction) {
                $points = $this->calculatePointsForSinglePrediction($fixture, $prediction);
                $prediction->update(['points_awarded' => $points]);
            }

            // Mark the fixture as processed to avoid recalculating
            $fixture->update(['points_calculated' => true]);
            $this->info("Finished processing fixture ID: {$fixture->id}. Points awarded.");
        }

        $this->info('All fixtures processed successfully!');
    }

    /**
     * Defines the scoring rules and calculates points for a single prediction.
     */
    private function calculatePointsForSinglePrediction(Fixture $fixture, Prediction $prediction): int
    {
        // Rule 1: Exact Score (e.g., predicted 2-1, actual was 2-1)
        if ($prediction->home_score_predicted === $fixture->home_score && $prediction->away_score_predicted === $fixture->away_score) {
            return 5; // 5 points for a perfect score
        }

        // Determine the actual and predicted outcomes (Home Win, Away Win, Draw)
        $actualOutcome = $this->getMatchOutcome($fixture->home_score, $fixture->away_score);
        $predictedOutcome = $this->getMatchOutcome($prediction->home_score_predicted, $prediction->away_score_predicted);

        // Rule 2: Correct Outcome AND Correct Goal Difference (e.g., predicted 4-2, actual was 2-0)
        if ($actualOutcome === $predictedOutcome && $actualOutcome !== 'D') {
            $actualGoalDifference = abs($fixture->home_score - $fixture->away_score);
            $predictedGoalDifference = abs($prediction->home_score_predicted - $prediction->away_score_predicted);
            
            if ($actualGoalDifference === $predictedGoalDifference) {
                return 3; // 3 points for correct outcome + correct goal difference
            }
        }

        // Rule 3: Correct Outcome only (e.g., predicted a home win, and the home team won)
        if ($actualOutcome === $predictedOutcome) {
            return 2; // 2 points for the correct result
        }

        // Rule 4: Incorrect Outcome
        return 0;
    }

    /**
     * Helper function to determine the outcome of a match.
     * Returns 'H' for Home Win, 'A' for Away Win, 'D' for Draw.
     */
    private function getMatchOutcome(int $homeScore, int $awayScore): string
    {
        if ($homeScore > $awayScore) {
            return 'H'; // Home Win
        }

        if ($awayScore > $homeScore) {
            return 'A'; // Away Win
        }

        return 'D'; // Draw
    }
}
