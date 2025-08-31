<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'bio',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the predictions made by this user.
     */
    public function predictions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    /**
     * Get the comments made by this user.
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the comment reactions made by this user.
     */
    public function commentReactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    /**
     * Get the notifications for this user.
     */
    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notifications count.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Get users that this user is following.
     */
    public function following(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
                    ->withTimestamps();
    }

    /**
     * Get users that are following this user.
     */
    public function followers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
                    ->withTimestamps();
    }

    /**
     * Check if this user is following another user.
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Check if this user is followed by another user.
     */
    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    /**
     * Get total predictions count.
     */
    public function getTotalPredictionsCount(): int
    {
        return $this->predictions()->count();
    }

    /**
     * Get correct predictions count.
     */
    public function getCorrectPredictionsCount(): int
    {
        return $this->predictions()
                    ->whereNotNull('points_awarded')
                    ->where('points_awarded', '>', 0)
                    ->count();
    }

    /**
     * Get prediction accuracy percentage.
     */
    public function getPredictionAccuracy(): float
    {
        $total = $this->getTotalPredictionsCount();
        if ($total === 0) return 0;
        
        return round(($this->getCorrectPredictionsCount() / $total) * 100, 1);
    }

    /**
     * Get total points earned.
     */
    public function getTotalPoints(): int
    {
        return $this->predictions()->sum('points_awarded') ?? 0;
    }

    /**
     * Get current season points.
     */
    public function getCurrentSeasonPoints(): int
    {
        return $this->predictions()
                    ->whereHas('fixture.season', function($query) {
                        $query->where('is_active', true);
                    })
                    ->sum('points_awarded') ?? 0;
    }

    /**
     * Get current season rank.
     */
    public function getCurrentSeasonRank(): ?int
    {
        $users = User::withSum(['predictions' => function($query) {
            $query->whereHas('fixture.season', function($q) {
                $q->where('is_active', true);
            });
        }], 'points_awarded')
        ->orderByDesc('predictions_sum_points_awarded')
        ->pluck('id');

        $rank = $users->search($this->id);
        return $rank !== false ? $rank + 1 : null;
    }

    /**
     * Get all-time rank.
     */
    public function getAllTimeRank(): ?int
    {
        $users = User::withSum('predictions', 'points_awarded')
                    ->orderByDesc('predictions_sum_points_awarded')
                    ->pluck('id');

        $rank = $users->search($this->id);
        return $rank !== false ? $rank + 1 : null;
    }

    /**
     * Get recent predictions (last 10).
     */
    public function getRecentPredictions(int $limit = 10)
    {
        return $this->predictions()
                    ->with(['fixture.homeTeam', 'fixture.awayTeam', 'fixture.season'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get recent comments (last 10).
     */
    public function getRecentComments(int $limit = 10)
    {
        return $this->comments()
                    ->with(['fixture.homeTeam', 'fixture.awayTeam', 'fixture.season'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get route key name for username-based routing.
     */
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    /**
     * Get user's best prediction streak.
     */
    public function getBestPredictionStreak(): int
    {
        $predictions = $this->predictions()
                            ->whereNotNull('points_awarded')
                            ->orderBy('created_at', 'asc')
                            ->get();

        $currentStreak = 0;
        $bestStreak = 0;

        foreach ($predictions as $prediction) {
            if ($prediction->points_awarded > 0) {
                $currentStreak++;
                $bestStreak = max($bestStreak, $currentStreak);
            } else {
                $currentStreak = 0;
            }
        }

        return $bestStreak;
    }

    /**
     * Get user's current prediction streak.
     */
    public function getCurrentPredictionStreak(): int
    {
        $predictions = $this->predictions()
                            ->whereNotNull('points_awarded')
                            ->orderBy('created_at', 'desc')
                            ->get();

        $currentStreak = 0;

        foreach ($predictions as $prediction) {
            if ($prediction->points_awarded > 0) {
                $currentStreak++;
            } else {
                break;
            }
        }

        return $currentStreak;
    }

    /**
     * Check if user has predictions for current season.
     */
    public function hasCurrentSeasonPredictions(): bool
    {
        return $this->predictions()
                    ->whereHas('fixture.season', function($query) {
                        $query->where('is_active', true);
                    })
                    ->exists();
    }

    /**
     * Get user's favorite team (most predicted for).
     */
    public function getFavoriteTeam()
    {
        return $this->predictions()
                    ->join('fixtures', 'predictions.fixture_id', '=', 'fixtures.id')
                    ->join('teams as home_teams', 'fixtures.home_team_id', '=', 'home_teams.id')
                    ->join('teams as away_teams', 'fixtures.away_team_id', '=', 'away_teams.id')
                    ->selectRaw('
                        CASE 
                            WHEN fixtures.home_team_id = predictions.home_score_predicted > predictions.away_score_predicted THEN home_teams.name
                            ELSE away_teams.name
                        END as team_name,
                        COUNT(*) as prediction_count
                    ')
                    ->whereNotNull('points_awarded')
                    ->where('points_awarded', '>', 0)
                    ->groupBy('team_name')
                    ->orderByDesc('prediction_count')
                    ->first();
    }

    /**
     * Get the badges earned by this user.
     */
    public function badges(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
                    ->withPivot('awarded_at', 'awarded_for')
                    ->withTimestamps()
                    ->orderByPivot('awarded_at', 'desc');
    }

    /**
     * Check if user has a specific badge.
     */
    public function hasBadge(string $badgeKey): bool
    {
        return $this->badges()->where('badges.key', $badgeKey)->exists();
    }

    /**
     * Award a badge to this user.
     */
    public function awardBadge(string $badgeKey, array $context = []): bool
    {
        $badge = Badge::findByKey($badgeKey);
        if (!$badge || $this->hasBadge($badgeKey)) {
            return false;
        }

        $this->badges()->attach($badge->id, [
            'awarded_at' => now(),
            'awarded_for' => json_encode($context),
        ]);

        return true;
    }

    /**
     * Get badges grouped by tier.
     */
    public function getBadgesByTier(): array
    {
        $badges = $this->badges()->get()->groupBy('tier');
        
        return [
            'diamond' => $badges->get('diamond', collect()),
            'gold' => $badges->get('gold', collect()),
            'silver' => $badges->get('silver', collect()),
            'bronze' => $badges->get('bronze', collect()),
        ];
    }

    /**
     * Get total badges count.
     */
    public function getBadgesCount(): int
    {
        return $this->badges()->count();
    }

    /**
     * Get recent badges (last 5).
     */
    public function getRecentBadges(int $limit = 5)
    {
        return $this->badges()
                    ->orderByPivot('awarded_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get user statistics for badge calculation.
     */
    public function getBadgeStats(): array
    {
        return [
            'predictions_count' => $this->getTotalPredictionsCount(),
            'comments_count' => $this->comments()->count(),
            'followers_count' => $this->followers()->count(),
            'following_count' => $this->following()->count(),
            'accuracy_percentage' => $this->getPredictionAccuracy(),
            'best_streak' => $this->getBestPredictionStreak(),
            'current_streak' => $this->getCurrentPredictionStreak(),
            'season_rank' => $this->getCurrentSeasonRank(),
            'all_time_rank' => $this->getAllTimeRank(),
            'exact_scores' => $this->getExactScoresCount(),
            'has_bio' => !empty($this->bio),
            'has_avatar' => !empty($this->avatar),
        ];
    }

    /**
     * Get exact scores count.
     */
    public function getExactScoresCount(): int
    {
        return $this->predictions()
                    ->where('points_awarded', 5) // 5 points = exact score
                    ->count();
    }
}
