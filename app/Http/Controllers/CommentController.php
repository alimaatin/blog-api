<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class CommentController extends Controller
{
    use ApiResponseTrait;
    /**
     * [Admin]Return all Comments
     */
    public function index()
    {
        try {
            $comments = Comment::all();
            return $this->successResponse($comments, 200);
        } catch (Throwable $e) {
            return $this->errorResponse("Error fetching comments", 500);
        }
    }

    /**
     * Return Comments by Post
     * @unauthenticated
     */
    public function find(Post $post)
    {
        try {
            $comment = $post->comments;
            foreach ($comment as $c) {
                $c->user;
                $c->post;
                $c->parent;
            }
            return $this->successResponse($comment, 200);
        } catch (Throwable $e) {
            return $this->errorResponse("Error fetching comments", 500);
        }
    }

    /**
     * Create a new Comment
     */
    public function store(StoreCommentRequest $request)
    {
        try {
        $validated = $request->validated();

        $user = auth()->user();

        $comment = $user->comments()->create($validated);
        return $this->successResponse(["message" => "Comment created","comment" => $comment], 201);
        } catch (Throwable $e) {
            return $this->errorResponse("Error creating comment", 500);
        }
    }

    /**
     * Update a Comment
     */
    public function update(Request $request, Comment $comment)
    {
        try {
            $validated = $request->validate([
                "content" => "nullable|string",
            ]);

            $changes = array_filter($validated, function ($value, $key) use ($comment) {
                return $comment->$key !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (empty($changes)) {
                return $this->errorResponse("No changes detected", 422);
            }

            $comment->update($changes);

            return $this->successResponse(["updated" => array_keys($changes), "comment" => $comment], 200);

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse("Comment not found", 404);
        } catch (Throwable $e) {
            return $this->errorResponse("Error updating comment", 500);
        }
    }

    /**
     * Delete a Comment
     */
    public function destroy(Comment $comment)
    {
        try {
            $comment->delete();
            return $this->successResponse(["message" => "Comment deleted", "comment" => $comment], 200);
        } catch (Throwable $e) {
            return $this->errorResponse("Error deleting comment", 500);
        }
    }
}
