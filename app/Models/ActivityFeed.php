<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityFeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'data',
        'activity_date',
        'is_public',
    ];

    protected $casts = [
        'data' => 'array',
        'activity_date' => 'datetime',
        'is_public' => 'boolean',
    ];

    /**
     * Get the user who performed this activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a prediction activity.
     */
    public static function createPredictionActivity(User $user, Prediction $prediction): self
    {
        return self::create([
            'user_id' => $user->id,
            'activity_type' => 'prediction',
            'data' => [
                'prediction_id' => $prediction->id,
                'fixture_id' => $prediction->fixture_id,
                'home_score_predicted' => $prediction->home_score_predicted,
                'away_score_predicted' => $prediction->away_score_predicted,
                'fixture' => [
                    'home_team' => $prediction->fixture->homeTeam->name,
                    'away_team' => $prediction->fixture->awayTeam->name,
                    'match_datetime' => $prediction->fixture->match_datetime,
                ],
            ],
            'activity_date' => $prediction->created_at,
            'is_public' => true,
        ]);
    }

    /**
     * Create a badge earned activity.
     */
    public static function createBadgeActivity(User $user, Badge $badge, array $context = []): self
    {
        return self::create([
            'user_id' => $user->id,
            'activity_type' => 'badge_earned',
            'data' => [
                'badge_id' => $badge->id,
                'badge_key' => $badge->key,
                'badge_name' => $badge->name,
                'badge_description' => $badge->description,
                'badge_icon' => $badge->icon,
                'badge_tier' => $badge->tier,
                'badge_category' => $badge->category,
                'context' => $context,
            ],
            'activity_date' => now(),
            'is_public' => true,
        ]);
    }

    /**
     * Create a follow activity.
     */
    public static function createFollowActivity(User $follower, User $following): self
    {
        return self::create([
            'user_id' => $follower->id,
            'activity_type' => 'follow',
            'data' => [
                'following_user_id' => $following->id,
                'following_user_name' => $following->name,
                'following_user_username' => $following->username,
                'following_user_avatar' => $following->avatar,
            ],
            'activity_date' => now(),
            'is_public' => true,
        ]);
    }

    /**
     * Scope for public activities only.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for activities by activity type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope for recent activities.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('activity_date', '>=', now()->subDays($days));
    }
}
