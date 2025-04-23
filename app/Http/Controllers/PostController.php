<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class PostController extends Controller
{
    /**
     * Return all Posts by filters
     * @unauthenticated
     */
    #[QueryParameter('user', description: 'Username of the author.', type: 'string', default:'alimatin', example: 'alimatin')]
    #[QueryParameter('search', description: 'Search term for post titles', type: 'string', example: 'hello')]
    #[QueryParameter('sort', description: 'How you want to sort the content(asc, desc)', type: 'string', default: 'desc', example: 'asc')]
    public function index(Request $request)
    {
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

        $query->where("state", "accepted");

        $sortOrder = $request->sort === "asc" ? "asc" : "desc";
        $query->orderBy("created_at", $sortOrder);
        
        $result = $query->get();

        return response()->json($result);
    }

    /**
     * Create a new Post
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "content" => "required|string",
                "thumbnail" => "required|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
                "category_id" => "nullable|integer|exists:categories,id",
        ]);

        $validated["user_id"] = JWTAuth::user()->id;

        $path = $request->file("thumbnail")->store("posts", "public");

        $validated["thumbnail"] = asset("storage/" . $path);

        $post = Post::create($validated);
        $post->user;
        $category = $post->category;
        $category->parent;

            return response()->json(["message" => "Post created","post" => $post], 201);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error creating post"], 500);
        }
    }

    /**
     * Return a Post by ID
     * @unauthenticated
     */
    public function find($id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->user;
            $category = $post->category;
            $category->parent;

            return response()->json($post);
        } catch(ModelNotFoundException $e) {
            return response()->json(["error" => "Post not found"], 404);
        }
    }

    /**
     * Update a Post
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                "title" => "nullable|string|max:255",
                "content" => "nullable|string",
                "category_id" => "nullable|integer|exists:categories,id",
            ]);
    
            $post = Post::findOrFail($id);
    
            $changes = array_filter($validated, function ($value, $key) use ($post) {   
                return $post->$key !== $value;
            }, ARRAY_FILTER_USE_BOTH);
    
            if (empty($changes)) {
                return response()->json(["error" => "No changes detected"], 422);
            }

            $post->update($changes);
            $post->user;
            $category = $post->category;
            $category->parent;
    
            return response()->json(["updated" => array_keys($changes), "post" => $post]);
    
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Post not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error updating post"], 500);
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
            $post->user;
            $category = $post->category;
            $category->parent;

            return response()->json(["message" => "Post deleted", "post" => $post] );
        } catch(ModelNotFoundException $e) {
            return response()->json(["error" => "Post not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error deleting post"], 500);
        }
    }
}
