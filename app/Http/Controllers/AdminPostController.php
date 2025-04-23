<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class AdminPostController extends Controller
{
    /**
     * Return all Posts
     */
    #[QueryParameter('user', description: 'Username of the author.', type: 'string', default:'alimatin', example: 'alimatin')]
    #[QueryParameter('search', description: 'Search term for post titles', type: 'string', example: 'hello')]
    #[QueryParameter('sort', description: 'How you want to sort the content(asc, desc)', type: 'string', default: 'desc', example: 'asc')]
    #[QueryParameter('state', description: 'State of the post', type: 'string', default: 'pending', example: 'accepted')]
    public function index(Request $request)
    {
        try {
            $query = Post::query();
    
            if ($request->has("user")) {
                $user = User::where("username", $request->user)->first();
                if ($user) {
                    $query->where("user_id", $user->id);
                }
            }
    
            if ($request->has("search")) {
                $query->where("title", "like", "%" . $request->search . "%");
            }

            if($request->has("state")) {
                $query->where("state", $request->state);
            }
    
            $sortOrder = $request->sort === "asc" ? "asc" : "desc";
            $query->orderBy("created_at", $sortOrder);

            $result = $query->get();
    
            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error fetching posts: " . $e->getMessage()], 500);
        }
    }
    
    /**
     * Return a Post by ID
     */
    public function find($id)
    {
        try {
            $post = Post::findOrFail($id);
            return response()->json($post);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Post not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error fetching post"], 500);
        }
    }

    /**
     * Accept a pending Post
     */
    public function accept($id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->state = "accepted";
            $post->save();
            return response()->json(["message" => "Post accepted", "post" => $post], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Post not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error accepting post"], 500);
        }
    }

    /**
     * Reject a pending Post
     */
    public function reject(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->state = "rejected";
            $post->save();
            return response()->json(["message" => "Post rejected", "post" => $post], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Post not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error rejecting post"], 500);
        }
    }
    
    /**
     * Delete a Post
     */
    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->delete();
            return response()->json(["message" => "Post deleted", "post" => $post], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Post not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error deleting post"], 500);
        }
    }
}
