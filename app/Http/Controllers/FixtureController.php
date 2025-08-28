<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;


class FixtureController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Determine the matchweek to show
        $targetMatchweek = $request->input('matchweek');

        if (!$targetMatchweek) {
            // If no matchweek is specified, find the first upcoming one
            $nextFixture = Fixture::where('match_datetime', '>', now())
                                  ->orderBy('match_datetime', 'asc')
                                  ->first();
            $targetMatchweek = $nextFixture ? $nextFixture->matchweek : 1; // Default to 1 if season hasn't started
        }

        // 2. Fetch fixtures for that matchweek
        $fixtures = Fixture::with(['homeTeam', 'awayTeam', 'venue'])
            ->where('matchweek', $targetMatchweek)
            ->orderBy('match_datetime', 'asc')
            ->get();

        // 3. Fetch the current user's predictions for these fixtures
        $userPredictions = Auth::user()->predictions()
            ->whereIn('fixture_id', $fixtures->pluck('id'))
            ->get()
            ->keyBy('fixture_id');

        // 4. Attach predictions to each fixture and check if it's locked
        $fixtures->transform(function ($fixture) use ($userPredictions) {
            $fixture->prediction = $userPredictions->get($fixture->id);
            // Predictions lock 1 hour before kickoff
            $fixture->is_locked = Carbon::parse($fixture->match_datetime)->isBefore(now()->addHour());
            return $fixture;
        });

        return Inertia::render('Fixtures/Index', [
            'fixtures' => $fixtures,
            'matchweek' => (int)$targetMatchweek,
        ]);
    }

    /**
     * Display the specified fixture with detailed information and predictions.
     */
    public function show($id): Response
    {
        // 1. Fetch the fixture with all related data
        $fixture = Fixture::with(['homeTeam', 'awayTeam', 'venue', 'season'])
            ->findOrFail($id);

        // 2. Get current user's prediction for this fixture
        $userPrediction = Auth::user()->predictions()
            ->where('fixture_id', $fixture->id)
            ->first();

        // 3. Calculate prediction statistics (win/lose/draw percentages)
        $totalPredictions = $fixture->predictions()->count();
        
        $predictionStats = [
            'total' => $totalPredictions,
            'home_wins' => 0,
            'draws' => 0,
            'away_wins' => 0,
            'home_win_percentage' => 0,
            'draw_percentage' => 0,
            'away_win_percentage' => 0,
        ];

        if ($totalPredictions > 0) {
            // Count predictions by outcome
            $predictions = $fixture->predictions()
                ->selectRaw('
                    SUM(CASE WHEN home_score_predicted > away_score_predicted THEN 1 ELSE 0 END) as home_wins,
                    SUM(CASE WHEN home_score_predicted = away_score_predicted THEN 1 ELSE 0 END) as draws,
                    SUM(CASE WHEN home_score_predicted < away_score_predicted THEN 1 ELSE 0 END) as away_wins
                ')
                ->first();

            $predictionStats['home_wins'] = (int) $predictions->home_wins;
            $predictionStats['draws'] = (int) $predictions->draws;
            $predictionStats['away_wins'] = (int) $predictions->away_wins;
            
            // Calculate percentages
            $predictionStats['home_win_percentage'] = round(($predictionStats['home_wins'] / $totalPredictions) * 100, 1);
            $predictionStats['draw_percentage'] = round(($predictionStats['draws'] / $totalPredictions) * 100, 1);
            $predictionStats['away_win_percentage'] = round(($predictionStats['away_wins'] / $totalPredictions) * 100, 1);
        }

        // 4. Get popular score predictions
        $popularScores = $fixture->predictions()
            ->selectRaw('home_score_predicted, away_score_predicted, COUNT(*) as count')
            ->groupBy('home_score_predicted', 'away_score_predicted')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($prediction) use ($totalPredictions) {
                return [
                    'score' => $prediction->home_score_predicted . '-' . $prediction->away_score_predicted,
                    'home_score' => $prediction->home_score_predicted,
                    'away_score' => $prediction->away_score_predicted,
                    'count' => $prediction->count,
                    'percentage' => $totalPredictions > 0 ? round(($prediction->count / $totalPredictions) * 100, 1) : 0,
                ];
            });

        // 5. Check if predictions are locked
        $fixture->is_locked = Carbon::parse($fixture->match_datetime)->isBefore(now()->addHour());
        
        // 6. Add time until lock information
        $lockTime = Carbon::parse($fixture->match_datetime)->subHour();
        $fixture->time_until_prediction_locks = now()->diffInHours($lockTime, false);

        // 7. Attach user prediction
        $fixture->prediction = $userPrediction;

        return Inertia::render('Fixtures/Show', [
            'fixture' => $fixture,
            'predictionStats' => $predictionStats,
            'popularScores' => $popularScores,
        ]);
    }
}
