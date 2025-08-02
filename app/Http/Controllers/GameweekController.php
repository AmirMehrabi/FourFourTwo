<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class GameweekController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        // Get all matchweeks with their first fixture date for timeline
        $gameweeks = Fixture::select(
                'matchweek',
                DB::raw('MIN(match_datetime) as start_date'),
                DB::raw('MAX(match_datetime) as end_date'),
                DB::raw('COUNT(*) as fixture_count')
            )
            ->groupBy('matchweek')
            ->orderBy('matchweek')
            ->get();

        // Get user's prediction stats per matchweek
        $userPredictionStats = $user->predictions()
            ->select(
                'fixtures.matchweek',
                DB::raw('COUNT(predictions.id) as predictions_made'),
                DB::raw('SUM(CASE WHEN predictions.points_awarded IS NOT NULL THEN predictions.points_awarded ELSE 0 END) as points_earned'),
                DB::raw('COUNT(CASE WHEN predictions.points_awarded IS NOT NULL THEN 1 END) as predictions_completed')
            )
            ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
            ->groupBy('fixtures.matchweek')
            ->get()
            ->keyBy('matchweek');

        // Enhance gameweeks with prediction stats and status
        $gameweeks = $gameweeks->map(function ($gameweek) use ($userPredictionStats) {
            $stats = $userPredictionStats->get($gameweek->matchweek);
            
            $gameweek->predictions_made = $stats ? $stats->predictions_made : 0;
            $gameweek->points_earned = $stats ? $stats->points_earned : 0;
            $gameweek->predictions_completed = $stats ? $stats->predictions_completed : 0;
            
            // Determine status
            $now = now();
            $startDate = Carbon::parse($gameweek->start_date);
            $endDate = Carbon::parse($gameweek->end_date);
            
            if ($endDate->isPast()) {
                $gameweek->status = 'completed';
            } elseif ($startDate->isFuture()) {
                $gameweek->status = 'upcoming';
            } else {
                $gameweek->status = 'active';
            }
            
            // Check if predictions are still open (1 hour before first match)
            $gameweek->predictions_open = $startDate->subHour()->isFuture();
            
            return $gameweek;
        });

        // Find current matchweek
        $currentMatchweek = $gameweeks->firstWhere('status', 'active') ?? 
                           $gameweeks->firstWhere('status', 'upcoming') ?? 
                           $gameweeks->last();

        return Inertia::render('Gameweek/Index', [
            'gameweeks' => $gameweeks,
            'currentMatchweek' => $currentMatchweek ? $currentMatchweek->matchweek : 1,
        ]);
    }
}
