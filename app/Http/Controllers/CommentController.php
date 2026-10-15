<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Get comments for a report.
     */
    public function index(string $reportUuid): JsonResponse
    {
        $report = Report::where('uuid', $reportUuid)->firstOrFail();
        $currentUser = Auth::user();

        $comments = Comment::query()
            ->where('report_id', $report->id)
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->with([
                'user:id,name,avatar',
                'replies' => function ($query): void {
                    $query->where('is_hidden', false)
                        ->with('user:id,name,avatar')
                        ->latest('created_at');
                },
                'likes:id,comment_id,user_id',
            ])
            ->latest('created_at')
            ->paginate(15);

        $items = collect($comments->items())->map(function (Comment $comment) use ($currentUser) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'likes_count' => $comment->likes_count,
                'is_liked' => $currentUser ? $comment->likes->contains('user_id', $currentUser->id) : false,
                'created_at' => $comment->created_at?->toISOString(),
                'user' => [
                    'id' => $comment->user?->id,
                    'name' => $comment->user?->name ?? 'Anonymous',
                    'avatar' => $comment->user?->avatar,
                ],
                'can_delete' => $currentUser && ($currentUser->id === $comment->user_id || (method_exists($currentUser, 'hasRole') && $currentUser->hasAnyRole(['super_admin', 'moderator']))),
                'replies' => $comment->replies->map(function (Comment $reply) use ($currentUser) {
                    return [
                        'id' => $reply->id,
                        'parent_id' => $reply->parent_id,
                        'content' => $reply->content,
                        'likes_count' => $reply->likes_count,
                        'is_liked' => $currentUser ? $reply->likes()->where('user_id', $currentUser->id)->exists() : false,
                        'created_at' => $reply->created_at?->toISOString(),
                        'user' => [
                            'id' => $reply->user?->id,
                            'name' => $reply->user?->name ?? 'Anonymous',
                            'avatar' => $reply->user?->avatar,
                        ],
                        'can_delete' => $currentUser && ($currentUser->id === $reply->user_id || (method_exists($currentUser, 'hasRole') && $currentUser->hasAnyRole(['super_admin', 'moderator']))),
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $items,
            'total' => $comments->total(),
            'has_more' => $comments->hasMorePages(),
            'current_page' => $comments->currentPage(),
        ]);
    }

    /**
     * Store a new comment or reply.
     */
    public function store(Request $request, string $reportUuid): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $report = Report::where('uuid', $reportUuid)->firstOrFail();

        $comment = Comment::create([
            'report_id' => $report->id,
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => trim($validated['content']),
            'likes_count' => 0,
            'is_hidden' => false,
        ]);

        $comment->load('user:id,name,avatar');

        return response()->json([
            'id' => $comment->id,
            'parent_id' => $comment->parent_id,
            'content' => $comment->content,
            'likes_count' => 0,
            'is_liked' => false,
            'created_at' => $comment->created_at?->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ],
            'can_delete' => true,
            'replies' => [],
        ], 201);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isAdmin = method_exists($user, 'hasRole') && $user->hasAnyRole(['super_admin', 'moderator']);

        if ($user->id !== $comment->user_id && ! $isAdmin) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $comment->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Toggle like on a comment.
     */
    public function toggleLike(Comment $comment): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $existingLike = CommentLike::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $comment->decrement('likes_count');
            $liked = false;
        } else {
            CommentLike::create([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
            ]);
            $comment->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => max(0, $comment->fresh()->likes_count),
        ]);
    }
}
