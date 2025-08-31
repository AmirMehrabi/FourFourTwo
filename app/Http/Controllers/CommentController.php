<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Fixture;
use App\Models\Mention;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    /**
     * Display comments for a fixture.
     */
    public function index(Request $request, Fixture $fixture): JsonResponse
    {
        $comments = $fixture->comments()
            ->with(['user', 'replies.user', 'reactions'])
            ->withCount('replies')
            ->paginate(20);

        // Add user reaction info to each comment
        $user = Auth::user();
        $comments->getCollection()->transform(function ($comment) use ($user) {
            $comment->user_reaction = $user ? 
                $comment->reactions()->where('user_id', $user->id)->first()?->type : null;
            $comment->reaction_counts = $comment->getReactionCounts();
            
            // Transform replies as well
            $comment->replies->transform(function ($reply) use ($user) {
                $reply->user_reaction = $user ? 
                    $reply->reactions()->where('user_id', $user->id)->first()?->type : null;
                $reply->reaction_counts = $reply->getReactionCounts();
                return $reply;
            });
            
            return $comment;
        });

        return response()->json($comments);
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'fixture_id' => 'required|exists:fixtures,id',
            'parent_id' => 'nullable|exists:comments,id',
            'content' => 'required|string|min:1|max:1000',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'fixture_id' => $request->input('fixture_id'),
            'parent_id' => $request->input('parent_id'),
            'content' => trim($request->input('content')),
        ]);

        // Load relationships for the response
        $comment->load(['user', 'reactions']);
        $comment->user_reaction = null;
        $comment->reaction_counts = [];
        $comment->replies_count = 0;

        // Create notification for replies
        if ($request->input('parent_id')) {
            $parentComment = Comment::with(['user', 'fixture.homeTeam', 'fixture.awayTeam'])
                ->find($request->input('parent_id'));
            
            if ($parentComment && $parentComment->user_id !== Auth::id()) {
                Notification::createCommentReplyNotification(
                    $parentComment->user_id,
                    $comment,
                    $parentComment
                );
            }
        }

        // Process mentions
        $this->processMentions($comment);

        return response()->json([
            'message' => 'Comment posted successfully',
            'comment' => $comment
        ], 201);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // Check if user owns the comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|min:1|max:1000',
        ]);

        $comment->update([
            'content' => trim($request->input('content')),
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Check if user owns the comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    /**
     * Toggle reaction on a comment.
     */
    public function toggleReaction(Request $request, Comment $comment): JsonResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(['like', 'love', 'laugh', 'angry', 'sad'])],
        ]);

        $userId = Auth::id();
        $type = $request->type;

        $existingReaction = $comment->reactions()
            ->where('user_id', $userId)
            ->first();

        if ($existingReaction) {
            if ($existingReaction->type === $type) {
                // Remove reaction if same type
                $existingReaction->delete();
                $userReaction = null;
            } else {
                // Update reaction type
                $existingReaction->update(['type' => $type]);
                $userReaction = $type;
                
                // Create notification for reaction change (only if not reacting to own comment)
                if ($comment->user_id !== $userId) {
                    \App\Models\Notification::createCommentReactionNotification(
                        $comment->user_id,
                        $existingReaction->fresh(['user', 'comment'])
                    );
                }
            }
        } else {
            // Create new reaction
            $newReaction = CommentReaction::create([
                'user_id' => $userId,
                'comment_id' => $comment->id,
                'type' => $type,
            ]);
            $userReaction = $type;
            
            // Create notification for new reaction (only if not reacting to own comment)
            if ($comment->user_id !== $userId) {
                \App\Models\Notification::createCommentReactionNotification(
                    $comment->user_id,
                    $newReaction->load(['user', 'comment'])
                );
            }
        }

        return response()->json([
            'message' => 'Reaction updated successfully',
            'user_reaction' => $userReaction,
            'reaction_counts' => $comment->fresh()->getReactionCounts()
        ]);
    }

    /**
     * Search followed users for mention autocomplete.
     */
    public function searchFollowedUsers(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $limit = min($request->get('limit', 10), 20); // Max 20 users
        
        if (strlen($query) < 1) {
            return response()->json([]);
        }
        
        $user = Auth::user();
        
        // Get users that the current user is following and match the search query
        $followedUsers = $user->following()
            ->where(function ($q) use ($query) {
                $q->where('users.username', 'LIKE', '%' . $query . '%')
                  ->orWhere('users.name', 'LIKE', '%' . $query . '%');
            })
            ->select('users.id', 'users.username', 'users.name', 'users.avatar')
            ->limit($limit)
            ->get();
        
        return response()->json($followedUsers);
    }

    /**
     * Process mentions in a comment and create notifications.
     */
    private function processMentions(Comment $comment): void
    {
        $content = $comment->content;
        $mentionedUsernames = [];
        
        // Extract usernames mentioned with @ symbol
        preg_match_all('/@(\w+)/', $content, $matches);
        
        if (empty($matches[1])) {
            return;
        }
        
        $mentionedUsernames = array_unique($matches[1]);
        
        // Get current user
        $currentUser = User::find($comment->user_id);
        
        // Get mentioned users that the current user is following
        $mentionedUsers = $currentUser->following()
            ->whereIn('users.username', $mentionedUsernames)
            ->where('users.id', '!=', $comment->user_id) // Don't notify self
            ->get();
        
        foreach ($mentionedUsers as $mentionedUser) {
            // Create mention record
            Mention::create([
                'comment_id' => $comment->id,
                'mentioned_user_id' => $mentionedUser->id,
            ]);
            
            // Create notification
            Notification::createMentionNotification(
                $mentionedUser->id,
                $comment->load(['user', 'fixture.homeTeam', 'fixture.awayTeam']),
                $mentionedUser
            );
        }
    }
}
