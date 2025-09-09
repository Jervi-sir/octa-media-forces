<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Http\Controllers\Controller;
use App\Models\QualificationComment;
use App\Models\QualificationLike;
use App\Models\QualificationRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QualificationCommentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    */
    public function listComments(Request $request, $type)
    {
        $qualification_read = QualificationRead::where('type', $type)->first();

        if (!$qualification_read) {
            return response()->json(['message' => 'Qualification read not found'], 404);
        }

        $perPage = $request->input('per_page', 10); // Default to 10 comments per page

        $comments = QualificationComment::where('qualification_read_id', $qualification_read->id)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'likes'])
            ->paginate($perPage);

        $formattedComments = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => $comment->user,
                'likes' => $comment->likes, // Use integer directly
                'is_liked' => $comment->likes()->where('ogm_id', Auth::id())->exists(),
                'created_at' => $comment->created_at->diffForHumans(),
                'replies' => $comment->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'user' => $reply->user,
                        'likes' => $reply->likes, // Use integer directly
                        'is_liked' => $reply->likes()->where('ogm_id', Auth::id())->exists(),
                        'created_at' => $reply->created_at->diffForHumans(),
                    ];
                }),
            ];
        });

        return response()->json([
            'comments' => $formattedComments,
            'current_page' => $comments->currentPage(),
            'next_page' => $comments->hasMorePages() ? $comments->currentPage() + 1 : null,
            'per_page' => $comments->perPage(),
            'total' => $comments->total(),
        ]);
    }

    public function storeComment(Request $request, $type)
    {
        $request->validate(['content' => 'required|string']);
        $qualification_read = QualificationRead::where('type', $type)->first();
        $comment = QualificationComment::create([
            'qualification_read_id' => $qualification_read->id,
            'ogm_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return response()->json([
            'id' => $comment->id,
            'content' => $comment->content,
            'user' => $comment->user,
            'likes' => 0,
            'is_liked' => false,
            'created_at' => $comment->created_at->diffForHumans(),
        ], 201);
    }

    public function replyComment(Request $request, $type, $comment_id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
        $qualification_read = QualificationRead::where('type', $type)->firstOrFail();
        $parentComment = QualificationComment::where('id', $comment_id)
            ->where('qualification_read_id', $qualification_read->id)
            ->firstOrFail();

        $comment = QualificationComment::create([
            'qualification_read_id' => $qualification_read->id,
            'ogm_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $comment_id,
        ]);

        return response()->json([
            'id' => $comment->id,
            'content' => $comment->content,
            'user' => $comment->user,
            'likes' => 0,
            'is_liked' => false,
            'created_at' => $comment->created_at->diffForHumans(),
        ], 201);
    }



    /*
    |--------------------------------------------------------------------------
    | Like
    |--------------------------------------------------------------------------
    */
    public function likeArticle($type)
    {
        $qualification_read = QualificationRead::where('type', $type)->firstOrFail();
        $userId = Auth::id();

        $like = QualificationLike::where('ogm_id', $userId)
            ->where('likeable_id', $qualification_read->id)
            ->where('likeable_type', QualificationRead::class)
            ->first();

        if ($like) {
            $like->delete();
            $qualification_read->decrement('likes');
            $isLiked = false;
        } else {
            QualificationLike::create([
                'ogm_id' => $userId,
                'likeable_id' => $qualification_read->id,
                'likeable_type' => QualificationRead::class,
            ]);
            $qualification_read->increment('likes');
            $isLiked = true;
        }

        return response()->json([
            'likes' => $qualification_read->likes,
            'is_liked' => $isLiked,
        ]);
    }

    public function likeComment($type, $comment_id)
    {
        $qualification_read = QualificationRead::where('type', $type)->firstOrFail();
        $comment = QualificationComment::findOrFail($comment_id);
        $userId = Auth::id();

        $like = QualificationLike::where('ogm_id', $userId)
            ->where('likeable_id', $comment_id)
            ->where('likeable_type', QualificationComment::class)
            ->first();

        if ($like) {
            $like->delete();
            $comment->decrement('likes');
            $isLiked = false;
        } else {
            QualificationLike::create([
                'ogm_id' => $userId,
                'likeable_id' => $comment_id,
                'likeable_type' => QualificationComment::class,
            ]);
            $comment->increment('likes');
            $isLiked = true;
        }

        return response()->json([
            'likes' => $comment->likes,
            'is_liked' => $isLiked,
        ]);
    }
}
