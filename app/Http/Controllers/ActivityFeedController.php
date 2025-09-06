<?php

namespace App\Http\Controllers;

use App\Models\ActivityFeed;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ActivityFeedController extends Controller
{
    /**
     * Display the activity feed page.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        // Get user's notifications with pagination
        $notifications = $user->notifications()
            ->paginate(20);

        // Mark all notifications as read when viewing the feed
        $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Get some stats for the feed
        $stats = [
            'total_notifications' => $user->notifications()->count(),
            'unread_count' => 0, // Will be 0 after marking all as read above
            'comments_count' => $user->comments()->count(),
            'predictions_count' => $user->predictions()->count(),
        ];

        return Inertia::render('ActivityFeed/Index', [
            'notifications' => $notifications,
            'stats' => $stats,
        ]);
    }

    /**
     * Get activity feed data for dashboard.
     */
    public function getFeedData(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        
        // Get activity feed from followed users
        $activities = $user->getFollowingActivityFeed($limit);
        
        // If user doesn't follow anyone or has no activities, get some popular activities
        if ($activities->isEmpty()) {
            $activities = ActivityFeed::with('user')
                                    ->public()
                                    ->recent(7) // Last 7 days
                                    ->orderBy('activity_date', 'desc')
                                    ->limit($limit)
                                    ->get();
        }

        return response()->json([
            'activities' => $activities,
            'has_more' => $activities->count() >= $limit,
        ]);
    }

    /**
     * Get trending activities for discovery.
     */
    public function getTrendingActivities(Request $request)
    {
        $limit = $request->get('limit', 5);
        
        // Get recent badge activities (they're usually exciting)
        $badgeActivities = ActivityFeed::with('user')
                                     ->ofType('badge_earned')
                                     ->public()
                                     ->recent(3) // Last 3 days
                                     ->orderBy('activity_date', 'desc')
                                     ->limit($limit)
                                     ->get();

        // Get recent prediction activities from top performers
        $topUsers = \App\Models\User::withSum('predictions', 'points_awarded')
                                   ->orderByDesc('predictions_sum_points_awarded')
                                   ->limit(10)
                                   ->pluck('id');

        $predictionActivities = ActivityFeed::with('user')
                                          ->ofType('prediction')
                                          ->whereIn('user_id', $topUsers)
                                          ->public()
                                          ->recent(1) // Last 1 day
                                          ->orderBy('activity_date', 'desc')
                                          ->limit($limit)
                                          ->get();

        $activities = $badgeActivities->merge($predictionActivities)
                                    ->sortByDesc('activity_date')
                                    ->take($limit);

        return response()->json([
            'activities' => $activities->values(),
        ]);
    }
}
