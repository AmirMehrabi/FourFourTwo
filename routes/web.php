<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\FixtureController;
use App\Http\Controllers\GameweekController;
use App\Http\Controllers\MyPredictionsController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

Route::get('/', [WelcomeController::class, 'index']);


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/fixtures', [FixtureController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('fixtures.index');

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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/debug-csrf', function (Request $request) {
    return response()->json([
        'cookies_from_browser' => $request->cookies->all(),
        'session_id_laravel' => Session::getId(),
        'session_data' => Session::all(),
        'csrf_token_in_session' => Session::token(),
        'csrf_token_meta_tag' => csrf_token(),
        'is_authenticated' => auth()->check(),
        'auth_user' => auth()->user(),
    ]);
});

require __DIR__ . '/auth.php';
