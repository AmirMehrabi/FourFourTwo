<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\FixtureController;
use App\Http\Controllers\GameweekController;
use App\Http\Controllers\LeagueTableController;
use App\Http\Controllers\MyPredictionsController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ActivityFeedController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

Route::get('/', [WelcomeController::class, 'index']);

// API Routes for League Table
Route::get('/api/league-table', [LeagueTableController::class, 'index'])->name('league-table.index');
Route::post('/api/league-table/update', [LeagueTableController::class, 'update'])->name('league-table.update');


Route::get('/teams/{slug}', [TeamController::class, 'show'])->name('teams.show');

// User Profile Routes
Route::get('/@{username}', [UserProfileController::class, 'show'])->name('user.profile.show');


Route::get('/debug-csrf', function (Request $request) {
    return response()->json([
        'cookies_from_browser' => [
            'XSRF-TOKEN'     => $request->cookie('XSRF-TOKEN'),
            'laravel_session' => $request->cookie(config('session.cookie')),
        ],
        'session_id_laravel' => Session::getId(),
        'session_data' => Session::all(),
        'csrf_token_in_session' => Session::token(),
        'csrf_token_meta_tag' => csrf_token(),
        'is_authenticated' => Auth::check(),
        'auth_user' => Auth::user(),
    ]);
})->middleware('web');
Route::post('/debug-csrf-post', function (Request $request) {
    return response()->json([
        'message' => 'POST succeeded — CSRF token was valid ✅',
        'posted_data' => $request->all(),
    ]);
})->middleware('web', 'auth');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/fixtures', [FixtureController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('fixtures.index');

Route::get('/fixtures/{id}', [FixtureController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('fixtures.show');

Route::get('/gameweek', [GameweekController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('gameweek.index');

Route::get('/my-predictions', [MyPredictionsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('my-predictions.index');

Route::put('/my-predictions/{prediction}', [MyPredictionsController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('my-predictions.update');

Route::middleware('auth')->group(function () {

    Route::post('/predictions', [PredictionController::class, 'store'])
        ->name('predictions.store');

    Route::put('/my-predictions/{prediction}', [MyPredictionsController::class, 'update'])
        ->name('my-predictions.update');

    Route::get('/leaderboard', [LeaderboardController::class, 'index'])
        ->name('leaderboard.index');

    Route::get('/activity', [ActivityFeedController::class, 'index'])
        ->name('activity.index');

    Route::get('/notifications', [App\Http\Controllers\NotificationsController::class, 'index'])
        ->name('notifications.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Comment routes
    Route::get('/fixtures/{fixture}/comments', [App\Http\Controllers\CommentController::class, 'index'])
        ->name('comments.index');
    Route::post('/comments', [App\Http\Controllers\CommentController::class, 'store'])
        ->name('comments.store');
    Route::put('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'update'])
        ->name('comments.update');
    Route::delete('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])
        ->name('comments.destroy');
    Route::post('/comments/{comment}/react', [App\Http\Controllers\CommentController::class, 'toggleReaction'])
        ->name('comments.react');
    Route::get('/comments/search-followed-users', [App\Http\Controllers\CommentController::class, 'searchFollowedUsers'])
        ->name('comments.search-followed-users');

    // Notification API routes
    Route::get('/api/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index'])
        ->name('api.notifications.index');
    Route::get('/api/notifications/unread-count', [App\Http\Controllers\Api\NotificationController::class, 'unreadCount'])
        ->name('api.notifications.unread-count');
    Route::post('/api/notifications/{notification}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead'])
        ->name('api.notifications.mark-as-read');
    Route::post('/api/notifications/mark-all-read', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead'])
        ->name('api.notifications.mark-all-read');

    // User Profile API Routes
    Route::post('/@{username}/follow', [UserProfileController::class, 'follow'])->name('user.profile.follow');
    Route::post('/@{username}/unfollow', [UserProfileController::class, 'unfollow'])->name('user.profile.unfollow');
    Route::get('/@{username}/followers', [UserProfileController::class, 'followers'])->name('user.profile.followers');
    Route::get('/@{username}/following', [UserProfileController::class, 'following'])->name('user.profile.following');
    
    // API routes for mentions
    Route::get('/api/user/following', [UserProfileController::class, 'getFollowingForMentions'])->name('api.user.following');
});


require __DIR__ . '/auth.php';
