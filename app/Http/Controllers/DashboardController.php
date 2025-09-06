<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\User;
use App\Models\LeagueTable;
use App\Models\Season;
use App\Services\LeagueTableService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        
        // 1. Get upcoming fixtures (next 5-10 matches)
        $upcomingFixtures = Fixture::with(['homeTeam', 'awayTeam', 'venue'])
            ->where('match_datetime', '>', now())
            ->orderBy('match_datetime', 'asc')
            ->take(8)
            ->get();

        // Get user's predictions for these upcoming fixtures
        $userPredictions = $user->predictions()
            ->whereIn('fixture_id', $upcomingFixtures->pluck('id'))
            ->get()
            ->keyBy('fixture_id');

        // Attach predictions and lock status to fixtures
        $upcomingFixtures->transform(function ($fixture) use ($userPredictions) {
            $fixture->prediction = $userPredictions->get($fixture->id);
            $fixture->is_locked = Carbon::parse($fixture->match_datetime)->isBefore(now()->addHour());
            
            // Add time remaining info
            $matchTime = Carbon::parse($fixture->match_datetime);
            $fixture->hours_until_match = now()->diffInHours($matchTime, false);
            $fixture->time_until_prediction_locks = now()->diffInHours($matchTime->subHour(), false);
            
            return $fixture;
        });

        // 2. Get user's recent predictions (last 5)
        $recentPredictions = $user->predictions()
            ->with(['fixture.homeTeam', 'fixture.awayTeam'])
            ->whereHas('fixture', function ($query) {
                $query->where('match_datetime', '<=', now()->addWeek()); // Within next week or already passed
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 3. Get leaderboard info
        $leaderboard = User::query()
            ->select('users.id', 'users.name', DB::raw('COALESCE(SUM(predictions.points_awarded), 0) as total_points'))
            ->leftJoin('predictions', 'users.id', '=', 'predictions.user_id')
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_points', 'desc')
            ->orderBy('users.name', 'asc')
            ->get();

        // Find user's rank
        $userRank = $leaderboard->search(function ($item) use ($user) {
            return $item->id === $user->id;
        }) + 1;

        $userPoints = $leaderboard->firstWhere('id', $user->id)->total_points ?? 0;

        // Get top 5 for display
        $topLeaderboard = $leaderboard->take(5);

        // 4. Get activity feed data
        $activityFeed = $user->getFollowingActivityFeed(8);
        
        // If no activities from following, get some trending activities
        if ($activityFeed->isEmpty()) {
            $activityFeed = \App\Models\ActivityFeed::with('user')
                                                  ->public()
                                                  ->recent(7)
                                                  ->orderBy('activity_date', 'desc')
                                                  ->limit(8)
                                                  ->get();
        }

        // 5. Get league table data
        $currentSeason = Season::where('is_active', true)->first();
        $leagueTable = collect([]);
        
        if ($currentSeason) {
            $leagueTableService = new LeagueTableService();
            $leagueTable = $leagueTableService->getLiveTable($currentSeason->id);
        }

        // 6. Get user stats for gamification
        $userStats = [
            'current_streak' => $user->getCurrentPredictionStreak(),
            'best_streak' => $user->getBestPredictionStreak(),
            'accuracy' => $user->getPredictionAccuracy(),
            'badges_count' => $user->getBadgesCount(),
            'recent_badges' => $user->getRecentBadges(3),
            'following_count' => $user->following()->count(),
            'followers_count' => $user->followers()->count(),
        ];

        return Inertia::render('Dashboard/Index', [
            'upcomingFixtures' => $upcomingFixtures,
            'recentPredictions' => $recentPredictions,
            'userRank' => $userRank,
            'userPoints' => $userPoints,
            'topLeaderboard' => $topLeaderboard,
            'totalUsers' => $leaderboard->count(),
            'activityFeed' => $activityFeed,
            'userStats' => $userStats,
            'leagueTable' => $leagueTable,
        ]);
    }
}
