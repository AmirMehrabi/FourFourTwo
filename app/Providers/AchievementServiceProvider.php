<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\CheckUserAchievements;
use App\Events\UserFollowed;
use App\Models\Prediction;
use App\Models\Comment;
use App\Models\User;

class AchievementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register model event listeners for automatic badge checking
        $this->registerModelEvents();
    }

    /**
     * Register model events for achievement checking.
     */
    protected function registerModelEvents(): void
    {
        // Prediction events
        Prediction::created(function ($prediction) {
            Event::dispatch('achievement.prediction.created', $prediction);
        });

        Prediction::updated(function ($prediction) {
            Event::dispatch('achievement.prediction.updated', $prediction);
        });

        // Comment events
        Comment::created(function ($comment) {
            Event::dispatch('achievement.comment.created', $comment);
        });

        // User events (for profile completion)
        User::updated(function ($user) {
            Event::dispatch('achievement.user.updated', $user);
        });

        // Register event listeners
        Event::listen('achievement.prediction.created', [CheckUserAchievements::class, 'handlePredictionCreated']);
        Event::listen('achievement.prediction.updated', [CheckUserAchievements::class, 'handlePredictionUpdated']);
        Event::listen('achievement.comment.created', [CheckUserAchievements::class, 'handleCommentCreated']);
        Event::listen('achievement.user.updated', [CheckUserAchievements::class, 'handleProfileUpdated']);
        Event::listen(UserFollowed::class, [CheckUserAchievements::class, 'handleUserFollowed']);
    }
}
