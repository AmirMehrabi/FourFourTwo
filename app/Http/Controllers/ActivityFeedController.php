<?php

namespace App\Http\Controllers;

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
}
