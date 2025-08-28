<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fixture_id',
        'parent_id',
        'content',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    protected $with = ['user'];

    /**
     * Get the user who made the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fixture this comment belongs to.
     */
    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get all reactions to this comment.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    /**
     * Check if this comment is a reply.
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get the count of replies.
     */
    public function repliesCount(): int
    {
        return $this->replies()->count();
    }

    /**
     * Get reactions count by type.
     */
    public function getReactionCounts(): array
    {
        return $this->reactions()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    /**
     * Check if a user has reacted to this comment.
     */
    public function hasUserReacted(int $userId, string $type = null): bool
    {
        $query = $this->reactions()->where('user_id', $userId);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->exists();
    }

    /**
     * Get all mentions in this comment.
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(Mention::class);
    }

    /**
     * Get all mentioned users in this comment.
     */
    public function mentionedUsers(): HasMany
    {
        return $this->belongsToMany(User::class, 'mentions', 'comment_id', 'mentioned_user_id');
    }
}
