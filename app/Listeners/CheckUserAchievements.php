<?php

namespace App\Listeners;

use App\Services\AchievementService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CheckUserAchievements
{
    protected $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Handle the event when a prediction is created.
     */
    public function handlePredictionCreated($event)
    {
        $prediction = $event->prediction ?? $event;
        $user = $prediction->user;
        
        $this->achievementService->checkForTrigger($user, 'prediction_created', [
            'prediction_id' => $prediction->id
        ]);
    }

    /**
     * Handle the event when a prediction is updated (points awarded).
     */
    public function handlePredictionUpdated($event)
    {
        $prediction = $event->prediction ?? $event;
        $user = $prediction->user;
        
        // Only check if points were just awarded
        if ($prediction->wasChanged('points_awarded') && $prediction->points_awarded !== null) {
            $this->achievementService->checkForTrigger($user, 'prediction_scored', [
                'prediction_id' => $prediction->id,
                'points_awarded' => $prediction->points_awarded
            ]);
        }
    }

    /**
     * Handle the event when a comment is created.
     */
    public function handleCommentCreated($event)
    {
        $comment = $event->comment ?? $event;
        $user = $comment->user;
        
        $this->achievementService->checkForTrigger($user, 'comment_created', [
            'comment_id' => $comment->id
        ]);
    }

    /**
     * Handle the event when a user follows another user.
     */
    public function handleUserFollowed($event)
    {
        $follower = $event->follower;
        $followed = $event->followed;
        
        // Check achievements for the follower (first_follow badge)
        $this->achievementService->checkForTrigger($follower, 'user_followed', [
            'followed_user_id' => $followed->id
        ]);
        
        // Check achievements for the followed user (social_butterfly, influencer badges)
        $this->achievementService->checkForTrigger($followed, 'gained_follower', [
            'follower_user_id' => $follower->id
        ]);
    }

    /**
     * Handle the event when a user's profile is updated.
     */
    public function handleProfileUpdated($event)
    {
        $user = $event->user ?? $event;
        
        $this->achievementService->checkForTrigger($user, 'profile_updated');
    }

    /**
     * Handle generic user activity events.
     */
    public function handleUserActivity($event)
    {
        $user = $event->user ?? $event;
        $activity = $event->activity ?? 'general';
        
        $this->achievementService->checkForTrigger($user, $activity);
    }
}
