<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class LikeController extends Controller
{

    private function updateLikesCount($postId, $type, $amount)
    {
        try {
            Post::findOrFail($postId);
            if ($type == "like") {
                Post::where("id", $postId)->increment("likes", $amount);
            } elseif ($type == "dislike") {
                Post::where("id", $postId)->increment("dislikes", $amount);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => $e->getMessage()], 404);
        }
    }

    /**
     * Like a Post or unlike if already liked
     */
    public function like(Request $request, $id)
    {
        try {
            $user = JWTAuth::user();
            $post = Post::findOrFail($id);

            $alreadyLiked = Like::where("user_id", $user->id)->where("post_id", $id)->first();

            if ($alreadyLiked) {
                if ($alreadyLiked->like) {
                    $alreadyLiked->delete();
                    $post->decrement('likes', 1);
                    return response()->json(["message" => "Unliked post successfully", "post" => $post]);
                }
            }

            $like = Like::create([
                "user_id" => $request->user()->id,
                "post_id" => $id,
                "like" => true
            ]);

            $post->increment('likes', 1);

            return response()->json(["message" => "Liked post successfully", "post" => $post]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Post not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error liking post"], 500);
        }
    }

    /**
     * Like a Post or undislike if already disliked
     */
    public function dislike(Request $request, $id)
    {
        try {
            $user = JWTAuth::user();
            $post = Post::findOrFail($id);

            $alreadyDisliked = Like::where("user_id", $user->id)->where("post_id", $id)->first();

            if ($alreadyDisliked) {
                $alreadyDisliked->delete();
                $post->decrement('dislikes', 1);
                return response()->json(["message" => "Undisliked post successfully", "post" => $post]);
            }

            $like = Like::create([
                "user_id" => $request->user()->id,
                "post_id" => $id,
                "like" => false
            ]);

            $post->increment('dislikes', 1);

            return response()->json(["message" => "Disliked post successfully", "post" => $post]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Post not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error disliking post"], 500);
        }
    }
    
}
