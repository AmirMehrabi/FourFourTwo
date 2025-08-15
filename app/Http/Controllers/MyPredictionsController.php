<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class MyPredictionsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $selectedGameweek = $request->get('gameweek', $this->getCurrentGameweek());
        
        // Get user's overall statistics
        $overallStats = $this->getUserOverallStats($user);
        
        // Get all gameweeks for the selector
        $gameweeks = $this->getGameweeksData($user);
        
        // Get predictions for selected gameweek
        $gameweekData = $this->getGameweekPredictions($user, $selectedGameweek);
        
        return Inertia::render('MyPredictions/Index', [
            'selectedGameweek' => $selectedGameweek,
            'overallStats' => $overallStats,
            'gameweeks' => $gameweeks,
            'gameweekData' => $gameweekData,
        ]);
    }

    private function getCurrentGameweek(): int
    {
        $currentFixture = Fixture::where('match_datetime', '>=', now())
            ->orderBy('match_datetime')
            ->first();
            
        return $currentFixture ? $currentFixture->matchweek : 1;
    }

    private function getUserOverallStats($user): array
    {
        $totalPredictions = $user->predictions()->count();
        $completedPredictions = $user->predictions()->whereNotNull('points_awarded')->count();
        $totalPoints = $user->predictions()->sum('points_awarded') ?? 0;
        $exactPredictions = $user->predictions()->where('points_awarded', 5)->count();
        $correctOutcomes = $user->predictions()->where('points_awarded', '>', 0)->count();
        
        // Calculate accuracy percentages
        $exactAccuracy = $completedPredictions > 0 ? round(($exactPredictions / $completedPredictions) * 100, 1) : 0;
        $outcomeAccuracy = $completedPredictions > 0 ? round(($correctOutcomes / $completedPredictions) * 100, 1) : 0;
        $avgPointsPerMatch = $completedPredictions > 0 ? round($totalPoints / $completedPredictions, 1) : 0;

        return [
            'total_predictions' => $totalPredictions,
            'completed_predictions' => $completedPredictions,
            'total_points' => $totalPoints,
            'exact_predictions' => $exactPredictions,
            'correct_outcomes' => $correctOutcomes,
            'exact_accuracy' => $exactAccuracy,
            'outcome_accuracy' => $outcomeAccuracy,
            'avg_points_per_match' => $avgPointsPerMatch,
        ];
    }

    private function getGameweeksData($user): array
    {
        return Fixture::select(
                'matchweek',
                DB::raw('MIN(match_datetime) as start_date'),
                DB::raw('COUNT(*) as total_fixtures')
            )
            ->groupBy('matchweek')
            ->orderBy('matchweek')
            ->get()
            ->map(function ($gameweek) use ($user) {
                $predictions = $user->predictions()
                    ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
                    ->where('fixtures.matchweek', $gameweek->matchweek)
                    ->count();
                
                $points = $user->predictions()
                    ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
                    ->where('fixtures.matchweek', $gameweek->matchweek)
                    ->sum('predictions.points_awarded') ?? 0;

                $now = now();
                $startDate = Carbon::parse($gameweek->start_date);
                
                if ($startDate->subHour()->isPast()) {
                    $status = $startDate->addDay()->isPast() ? 'completed' : 'active';
                } else {
                    $status = 'upcoming';
                }

                return [
                    'matchweek' => $gameweek->matchweek,
                    'start_date' => $gameweek->start_date,
                    'total_fixtures' => $gameweek->total_fixtures,
                    'predictions_made' => $predictions,
                    'points_earned' => $points,
                    'status' => $status,
                ];
            })
            ->toArray();
    }

    private function getGameweekPredictions($user, $gameweek): array
    {
        $fixtures = Fixture::with(['homeTeam', 'awayTeam', 'venue'])
            ->where('matchweek', $gameweek)
            ->orderBy('match_datetime')
            ->get();

        $userPredictions = $user->predictions()
            ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
            ->where('fixtures.matchweek', $gameweek)
            ->select('predictions.*')
            ->get()
            ->keyBy('fixture_id');

        $predictions = $fixtures->map(function ($fixture) use ($userPredictions) {
            $prediction = $userPredictions->get($fixture->id);
            
            // Calculate time until prediction locks (1 hour before match)
            $lockTime = Carbon::parse($fixture->match_datetime)->subHour();
            $now = now();
            $timeUntilLock = $now->diffInHours($lockTime, false);
            
            $isLocked = $timeUntilLock <= 0;
            $isCompleted = $fixture->home_score !== null && $fixture->away_score !== null;
            
            // Determine outcome if match is completed and user has prediction
            $outcome = null;
            if ($isCompleted && $prediction) {
                if ($prediction->points_awarded === null) {
                    $outcome = 'under_review'; // Fixture completed but points not calculated yet
                } elseif ($prediction->points_awarded === 5) {
                    $outcome = 'exact';
                } elseif ($prediction->points_awarded > 0) {
                    $outcome = 'correct_outcome';
                } else {
                    $outcome = 'wrong';
                }
            }

            return [
                'fixture' => $fixture,
                'prediction' => $prediction,
                'is_locked' => $isLocked,
                'is_completed' => $isCompleted,
                'time_until_lock' => max(0, $timeUntilLock),
                'outcome' => $outcome,
                'can_edit' => !$isLocked && !$isCompleted,
            ];
        });

        // Group by date for better organization
        $groupedPredictions = $predictions->groupBy(function ($item) {
            return Carbon::parse($item['fixture']->match_datetime)->format('Y-m-d');
        });

        return [
            'predictions' => $groupedPredictions->toArray(),
            'summary' => [
                'total_fixtures' => $fixtures->count(),
                'predictions_made' => $userPredictions->count(),
                'points_earned' => $userPredictions->sum('points_awarded') ?? 0,
                'editable_count' => $predictions->where('can_edit', true)->count(),
            ]
        ];
    }

    public function update(Request $request, Prediction $prediction)
    {
        $user = Auth::user();
        
        // Ensure user owns this prediction
        if ($prediction->user_id !== $user->id) {
            abort(403);
        }
        
        // Check if prediction is still editable
        $fixture = $prediction->fixture;
        $lockTime = Carbon::parse($fixture->match_datetime)->subHour();
        
        if (now()->gte($lockTime)) {
            return back()->withErrors(['message' => 'Prediction deadline has passed.']);
        }
        
        $request->validate([
            'home_score_predicted' => 'required|integer|min:0|max:10',
            'away_score_predicted' => 'required|integer|min:0|max:10',
        ]);
        
        $prediction->update([
            'home_score_predicted' => $request->home_score_predicted,
            'away_score_predicted' => $request->away_score_predicted,
        ]);
        
        return back()->with('success', 'Prediction updated successfully!');
    }
}
