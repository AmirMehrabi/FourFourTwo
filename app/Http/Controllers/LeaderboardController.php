<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index(): Response
    {
        // Get current week start (Monday) and end (Sunday)
        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $currentWeekEnd = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        
        // Get current season (assuming it's the most recent one)
        $currentSeason = DB::table('seasons')->orderBy('id', 'desc')->first();
        
        // Overall leaderboard (all time)
        $leaderboard = User::query()
            ->select('users.id', 'users.name', 'users.username', DB::raw('SUM(predictions.points_awarded) as total_points'))
            ->join('predictions', 'users.id', '=', 'predictions.user_id')
            ->whereNotNull('predictions.points_awarded')
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderBy('total_points', 'desc')
            ->orderBy('users.name', 'asc')
            ->get();

        // Weekly leaderboard (current week)
        $weeklyLeaderboard = User::query()
            ->select('users.id', 'users.name', 'users.username', DB::raw('SUM(predictions.points_awarded) as weekly_points'))
            ->join('predictions', 'users.id', '=', 'predictions.user_id')
            ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
            ->whereNotNull('predictions.points_awarded')
            ->whereBetween('fixtures.match_datetime', [$currentWeekStart, $currentWeekEnd])
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderBy('weekly_points', 'desc')
            ->orderBy('users.name', 'asc')
            ->get();

        // Season leaderboard (current season)
        $seasonLeaderboard = User::query()
            ->select('users.id', 'users.name', 'users.username', DB::raw('SUM(predictions.points_awarded) as season_points'))
            ->join('predictions', 'users.id', '=', 'predictions.user_id')
            ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
            ->whereNotNull('predictions.points_awarded')
            ->where('fixtures.season_id', $currentSeason?->id)
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderBy('season_points', 'desc')
            ->orderBy('users.name', 'asc')
            ->get();

        // Get highscorers for header
        $allTimeHighscorer = $leaderboard->first();
        $seasonHighscorer = $seasonLeaderboard->first();
        $weeklyHighscorer = $weeklyLeaderboard->first();

        return Inertia::render('Leaderboard/Index', [
            'leaderboard' => $leaderboard,
            'weeklyLeaderboard' => $weeklyLeaderboard,
            'seasonLeaderboard' => $seasonLeaderboard,
            'allTimeHighscorer' => $allTimeHighscorer,
            'seasonHighscorer' => $seasonHighscorer,
            'weeklyHighscorer' => $weeklyHighscorer,
        ]);
    }
}
