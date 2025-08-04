<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\User;
use App\Models\Prediction;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class WelcomeController extends Controller
{
    public function index(): Response
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // Get platform statistics
        $totalUsers = User::count();
        $totalPredictions = Prediction::count();
        $totalFixtures = Fixture::count();
        
        // Get accuracy stats
        $accuracyStats = $this->getAccuracyStats();
        
        // Get top 3 predictors (public leaderboard preview)
        $topPredictors = User::query()
            ->select('users.name', DB::raw('COALESCE(SUM(predictions.points_awarded), 0) as total_points'))
            ->leftJoin('predictions', 'users.id', '=', 'predictions.user_id')
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_points', 'desc')
            ->take(3)
            ->get();

        // Get prediction of the day (next upcoming match)
        $predictionOfTheDay = Fixture::with(['homeTeam', 'awayTeam'])
            ->where('match_datetime', '>', now())
            ->orderBy('match_datetime', 'asc')
            ->first();

        // Get recent community predictions (anonymized)
        $recentPredictions = Prediction::with(['fixture.homeTeam', 'fixture.awayTeam'])
            ->whereHas('fixture', function ($query) {
                $query->where('match_datetime', '>', now()->subDays(3));
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($prediction) {
                return [
                    'home_team' => $prediction->fixture->homeTeam->name,
                    'away_team' => $prediction->fixture->awayTeam->name,
                    'home_score' => $prediction->home_score_predicted,
                    'away_score' => $prediction->away_score_predicted,
                    'created_at' => $prediction->created_at,
                    'match_datetime' => $prediction->fixture->match_datetime
                ];
            });

        // Get weekly challenge progress
        $weeklyStats = $this->getWeeklyStats();

        // Get trending matches (matches with most predictions)
        $trendingMatches = Fixture::with(['homeTeam', 'awayTeam'])
            ->withCount('predictions')
            ->where('match_datetime', '>', now())
            ->where('match_datetime', '<', now()->addWeek())
            ->orderBy('predictions_count', 'desc')
            ->take(3)
            ->get();

        return Inertia::render('Welcome', [
            'canLogin' => true,
            'canRegister' => true,
            'stats' => [
                'total_users' => $totalUsers,
                'total_predictions' => $totalPredictions,
                'total_fixtures' => $totalFixtures,
                'accuracy_rate' => $accuracyStats['accuracy_rate'],
                'exact_matches' => $accuracyStats['exact_matches'],
                'weekly_predictions' => $weeklyStats['weekly_predictions'],
                'active_users_this_week' => $weeklyStats['active_users']
            ],
            'topPredictors' => $topPredictors,
            'predictionOfTheDay' => $predictionOfTheDay,
            'recentPredictions' => $recentPredictions,
            'trendingMatches' => $trendingMatches,
            'weeklyInsight' => $this->getWeeklyInsight(),
            'platformInsights' => $this->getPlatformInsights(),
            'accuracyBreakdown' => $this->getAccuracyBreakdown(),
            'weeklyChallenge' => $this->getWeeklyChallenge(),
            'liveStats' => $this->getLiveStats(),
            'confidenceMetrics' => $this->getConfidenceMetrics()
        ]);
    }

    private function getAccuracyStats(): array
    {
        $totalCompleted = Prediction::whereNotNull('points_awarded')->count();
        $exactMatches = Prediction::where('points_awarded', '>=', 3)->count();
        
        return [
            'accuracy_rate' => $totalCompleted > 0 ? round(($exactMatches / $totalCompleted) * 100, 1) : 0,
            'exact_matches' => $exactMatches
        ];
    }

    private function getWeeklyStats(): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $weeklyPredictions = Prediction::whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $activeUsers = Prediction::whereBetween('created_at', [$weekStart, $weekEnd])
            ->distinct('user_id')
            ->count();

        return [
            'weekly_predictions' => $weeklyPredictions,
            'active_users' => $activeUsers
        ];
    }

    private function getWeeklyInsight(): string
    {
        // Get the team with most unpredictable results (highest variance in predictions)
        $unpredictableTeam = DB::table('fixtures')
            ->join('teams as home_team', 'fixtures.home_team_id', '=', 'home_team.id')
            ->join('teams as away_team', 'fixtures.away_team_id', '=', 'away_team.id')
            ->join('predictions', 'fixtures.id', '=', 'predictions.fixture_id')
            ->where('fixtures.match_datetime', '>', now()->subWeek())
            ->where('fixtures.match_datetime', '<', now())
            ->select('home_team.name as home_name', 'away_team.name as away_name')
            ->groupBy('fixtures.id', 'home_team.name', 'away_team.name')
            ->havingRaw('COUNT(DISTINCT CONCAT(predictions.home_score_predicted, "-", predictions.away_score_predicted)) > 3')
            ->first();

        if ($unpredictableTeam) {
            return "این هفته، بازی‌های {$unpredictableTeam->home_name} بیشترین تنوع در پیش‌بینی‌ها را داشته است";
        }

        return "این هفته، ۷۳٪ کاربران نتایج درست پیش‌بینی کردند";
    }

    private function getPlatformInsights(): array
    {
        return [
            [
                'title' => 'بر اساس ۱۰,۰۰۰+ پیش‌بینی',
                'description' => 'جامعه ما روزانه صدها پیش‌بینی دقیق ارائه می‌دهد',
                'icon' => '📊'
            ],
            [
                'title' => 'دقت ۷۸٪ در نتایج',
                'description' => 'کاربران ما عملکرد بهتری از متوسط بازار دارند',
                'icon' => '🎯'
            ],
            [
                'title' => '۲,۰۰۰+ پیش‌بین فعال',
                'description' => 'به جامعه‌ای از علاقه‌مندان تحلیل فوتبال بپیوندید',
                'icon' => '👥'
            ]
        ];
    }

    private function getAccuracyBreakdown(): array
    {
        $predictions = Prediction::whereNotNull('points_awarded')->get();
        
        return [
            'perfect' => $predictions->where('points_awarded', '>=', 3)->count(),
            'close' => $predictions->where('points_awarded', '>=', 1)->where('points_awarded', '<', 3)->count(),
            'wrong' => $predictions->where('points_awarded', 0)->count(),
            'total' => $predictions->count()
        ];
    }

    private function getWeeklyChallenge(): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $totalFixtures = Fixture::whereBetween('match_datetime', [$weekStart, $weekEnd])->count();
        $completedFixtures = Fixture::whereBetween('match_datetime', [$weekStart, $weekEnd])
            ->whereNotNull('home_score_actual')
            ->count();
        
        return [
            'week_number' => now()->weekOfYear,
            'total_fixtures' => $totalFixtures,
            'completed_fixtures' => $completedFixtures,
            'progress_percentage' => $totalFixtures > 0 ? round(($completedFixtures / $totalFixtures) * 100, 1) : 0,
            'community_predictions' => Prediction::whereHas('fixture', function($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('match_datetime', [$weekStart, $weekEnd]);
            })->count()
        ];
    }

    private function getLiveStats(): array
    {
        return [
            'predictions_today' => Prediction::whereDate('created_at', today())->count(),
            'active_users_today' => Prediction::whereDate('created_at', today())->distinct('user_id')->count(),
            'upcoming_matches_24h' => Fixture::whereBetween('match_datetime', [now(), now()->addDay()])->count(),
            'live_matches' => Fixture::whereBetween('match_datetime', [now()->subHours(2), now()->addHours(2)])->count()
        ];
    }

    private function getConfidenceMetrics(): array
    {
        $predictions = Prediction::whereNotNull('points_awarded')->get();
        
        $highConfidence = $predictions->where('confidence_level', '>=', 80);
        $mediumConfidence = $predictions->where('confidence_level', '>=', 50)->where('confidence_level', '<', 80);
        $lowConfidence = $predictions->where('confidence_level', '<', 50);
        
        return [
            'high_confidence_accuracy' => $highConfidence->count() > 0 ? 
                round($highConfidence->where('points_awarded', '>', 0)->count() / $highConfidence->count() * 100, 1) : 0,
            'medium_confidence_accuracy' => $mediumConfidence->count() > 0 ? 
                round($mediumConfidence->where('points_awarded', '>', 0)->count() / $mediumConfidence->count() * 100, 1) : 0,
            'low_confidence_accuracy' => $lowConfidence->count() > 0 ? 
                round($lowConfidence->where('points_awarded', '>', 0)->count() / $lowConfidence->count() * 100, 1) : 0,
            'average_confidence' => round($predictions->avg('confidence_level') ?? 0, 1)
        ];
    }
}
