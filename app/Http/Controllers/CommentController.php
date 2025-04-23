<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class CommentController extends Controller
{
    /**
     * [Admin]Return all Comments
     */
    public function index(Request $request)
    {
        try {
            $comments = Comment::all();
            return response()->json($comments);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error fetching comments"], 500);
        }
    }   

    /**
     * Return Comments by Post ID
     * @unauthenticated
     */
    public function find($postId)
    {
        try {
            $comment = Comment::where("post_id", $postId)->get();
            foreach ($comment as $c) {
                $c->user;
                $c->post;
                $c->parent;
            }
            return response()->json($comment);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Comment not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error fetching comments"], 500);
        }
    }

    /**
     * Create a new Comment
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
            "content" => "required|string",
            "post_id" => "required|integer|exists:posts,id",
            "parent_id" => "nullable|integer|exists:comments,id",
        ]);

        $validated["user_id"] = JWTAuth::user()->id;
        
        $comment = Comment::create($validated);
            $comment->user;
            $comment->post;
            $comment->parent;
            return response()->json(["message" => "Comment created","comment" => $comment], 201);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error creating comment"], 500);
        }
    }

    /**
     * Update a Comment
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                "content" => "nullable|string",
            ]);

            $comment = Comment::findOrFail($id);

            $changes = array_filter($validated, function ($value, $key) use ($comment) {   
                return $comment->$key !== $value;
            }, ARRAY_FILTER_USE_BOTH);
    
            if (empty($changes)) {
                return response()->json(["error" => "No changes detected"], 422);
            }

            $comment->update($changes);
            $comment->user;
            $comment->post;
            $comment->parent;

            return response()->json(["updated" => array_keys($changes), "comment" => $comment]);

        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Comment not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error updating comment"], 500);
        }
    }

    /**
     * Delete a Comment
     */
    public function destroy($id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $comment->delete();
            return response()->json(["message" => "Comment deleted", "comment" => $comment], 200); 
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Comment not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error deleting comment"], 500);
        }
    }
}
