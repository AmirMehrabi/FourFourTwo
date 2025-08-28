<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(string $username)
    {
        try {
            $user = User::where('username', $username)->firstOrFail();
            $currentUser = Auth::user();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'User not found');
        }

        // Get user stats
        $stats = [
            'total_predictions' => $user->getTotalPredictionsCount(),
            'correct_predictions' => $user->getCorrectPredictionsCount(),
            'accuracy' => $user->getPredictionAccuracy(),
            'total_points' => $user->getTotalPoints(),
            'current_season_points' => $user->getCurrentSeasonPoints(),
            'current_season_rank' => $user->getCurrentSeasonRank(),
            'all_time_rank' => $user->getAllTimeRank(),
            'total_comments' => $user->comments()->count(),
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'best_streak' => $user->getBestPredictionStreak(),
            'current_streak' => $user->getCurrentPredictionStreak(),
            'has_current_season_predictions' => $user->hasCurrentSeasonPredictions(),
        ];

        // Get recent activity
        $recentPredictions = $user->getRecentPredictions(10);
        $recentComments = $user->getRecentComments(10);

        // Check if current user is following this profile
        $isFollowing = $currentUser ? $currentUser->isFollowing($user) : false;
        $isOwnProfile = $currentUser && $currentUser->id === $user->id;

        return Inertia::render('UserProfile', [
            'profileUser' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'bio' => $user->bio,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at->format('F Y'),
                'stats' => $stats,
                'recent_predictions' => $recentPredictions->map(function($prediction) {
                    return [
                        'id' => $prediction->id,
                        'home_team' => $prediction->fixture->homeTeam->name,
                        'away_team' => $prediction->fixture->awayTeam->name,
                        'home_score_predicted' => $prediction->home_score_predicted,
                        'away_score_predicted' => $prediction->away_score_predicted,
                        'home_score_actual' => $prediction->fixture->home_score,
                        'away_score_actual' => $prediction->fixture->away_score,
                        'points_awarded' => $prediction->points_awarded,
                        'fixture_date' => $prediction->fixture->match_datetime->format('M j, Y'),
                        'status' => $prediction->fixture->status,
                        'is_correct' => $prediction->points_awarded > 0,
                    ];
                }),
                'recent_comments' => $recentComments->map(function($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'fixture' => [
                            'home_team' => $comment->fixture->homeTeam->name,
                            'away_team' => $comment->fixture->awayTeam->name,
                        ],
                        'created_at' => $comment->created_at->diffForHumans(),
                        'reactions_count' => $comment->reactions()->count(),
                    ];
                }),
            ],
            'isFollowing' => $isFollowing,
            'isOwnProfile' => $isOwnProfile,
        ]);
    }

    /**
     * Follow a user.
     */
    public function follow(Request $request, string $username)
    {
        $userToFollow = User::where('username', $username)->firstOrFail();
        $currentUser = Auth::user();

        if ($currentUser->id === $userToFollow->id) {
            return response()->json(['error' => 'You cannot follow yourself'], 400);
        }

        if (!$currentUser->isFollowing($userToFollow)) {
            $currentUser->following()->attach($userToFollow->id);
        }

        return response()->json([
            'success' => true,
            'isFollowing' => true,
            'followersCount' => $userToFollow->followers()->count(),
        ]);
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(Request $request, string $username)
    {
        $userToUnfollow = User::where('username', $username)->firstOrFail();
        $currentUser = Auth::user();

        if ($currentUser->isFollowing($userToUnfollow)) {
            $currentUser->following()->detach($userToUnfollow->id);
        }

        return response()->json([
            'success' => true,
            'isFollowing' => false,
            'followersCount' => $userToUnfollow->followers()->count(),
        ]);
    }

    /**
     * Get user's followers.
     */
    public function followers(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();
        
        $followers = $user->followers()->paginate(20);

        return response()->json([
            'followers' => $followers->items(),
            'pagination' => [
                'current_page' => $followers->currentPage(),
                'last_page' => $followers->lastPage(),
                'per_page' => $followers->perPage(),
                'total' => $followers->total(),
            ],
        ]);
    }

    /**
     * Get users that the user is following.
     */
    public function following(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();
        
        $following = $user->following()->paginate(20);

        return response()->json([
            'following' => $following->items(),
            'pagination' => [
                'current_page' => $following->currentPage(),
                'last_page' => $following->lastPage(),
                'per_page' => $following->perPage(),
                'total' => $following->total(),
            ],
        ]);
    }

    /**
     * Get current user's following list for mentions (API endpoint).
     */
    public function getFollowingForMentions()
    {
        $currentUser = Auth::user();
        
        $following = $currentUser->following()
            ->select('id', 'name', 'username', 'avatar')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $following
        ]);
    }
}
