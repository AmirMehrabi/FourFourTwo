<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if the notification is unread.
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Create a comment reply notification.
     */
    public static function createCommentReplyNotification(int $userId, Comment $comment, Comment $parentComment): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'comment_reply',
            'data' => [
                'comment_id' => $comment->id,
                'parent_comment_id' => $parentComment->id,
                'fixture_id' => $comment->fixture_id,
                'replier_name' => $comment->user->name,
                'replier_username' => $comment->user->username,
                'content' => $comment->content,
                'fixture_teams' => [
                    'home' => $comment->fixture->homeTeam->name,
                    'away' => $comment->fixture->awayTeam->name,
                ],
            ],
        ]);
    }

    /**
     * Create a comment reaction notification.
     */
    public static function createCommentReactionNotification(int $userId, CommentReaction $reaction): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'comment_reaction',
            'data' => [
                'comment_id' => $reaction->comment_id,
                'reaction_type' => $reaction->type,
                'reactor_name' => $reaction->user->name,
                'reactor_username' => $reaction->user->username,
                'comment_content' => $reaction->comment->content,
            ],
        ]);
    }

    /**
     * Create a mention notification.
     */
    public static function createMentionNotification(int $userId, Comment $comment, User $mentionedUser): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'mention',
            'data' => [
                'comment_id' => $comment->id,
                'fixture_id' => $comment->fixture_id,
                'commenter_name' => $comment->user->name,
                'commenter_username' => $comment->user->username,
                'comment_content' => $comment->content,
                'fixture_teams' => [
                    'home' => $comment->fixture->homeTeam->name,
                    'away' => $comment->fixture->awayTeam->name,
                ],
                'mentioned_user_username' => $mentionedUser->username,
            ],
        ]);
    }

    /**
     * Create a new follower notification.
     */
    public static function createFollowerNotification(int $userId, User $follower): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'new_follower',
            'data' => [
                'follower_id' => $follower->id,
                'follower_name' => $follower->name,
                'follower_username' => $follower->username,
                'follower_avatar' => $follower->avatar,
                'follower_stats' => [
                    'predictions_count' => $follower->getTotalPredictionsCount(),
                    'accuracy' => $follower->getPredictionAccuracy(),
                    'badges_count' => $follower->getBadgesCount(),
                ],
            ],
        ]);
    }

    /**
     * Create a badge awarded notification.
     */
    public static function createBadgeNotification(int $userId, Badge $badge, array $context = []): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'badge_awarded',
            'data' => [
                'badge_id' => $badge->id,
                'badge_key' => $badge->key,
                'badge_name' => $badge->name,
                'badge_description' => $badge->description,
                'badge_icon' => $badge->icon,
                'badge_tier' => $badge->tier,
                'badge_category' => $badge->category,
                'badge_tier_color' => $badge->tier_color,
                'badge_rarity' => $badge->rarity,
                'context' => $context,
            ],
        ]);
    }
}
