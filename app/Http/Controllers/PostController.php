<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Repository\BaseRepository;
use App\Repository\PostRepository;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class PostController extends Controller
{
    use ApiResponseTrait;

    protected $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }
    /**
     * Return all Posts by filters
     * @unauthenticated
     */
    #[QueryParameter('user', description: 'Username of the author.', type: 'string', default:'alimatin', example: 'alimatin')]
    #[QueryParameter('search', description: 'Search term for post titles', type: 'string', example: 'hello')]
    #[QueryParameter('sort', description: 'How you want to sort the content(asc, desc)', type: 'string', default: 'desc', example: 'asc')]
    public function index(Request $request)
    {
        $posts = $this->postRepository->getByFilters($request->all());

        return $this->successResponse(PostResource::collection($posts), 200);
    }

    /**
     * Create a new Post
     */
    public function store(StorePostRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = auth()->user();

            $image = $request->file("thumbnail")->store("posts", "public");

            $validated["thumbnail"] = asset("storage/" . $image);

            $post = $user->posts()->create($validated);

            return $this->successResponse(["message" => "Post created","post" => new PostResource($post)], 201);
        } catch (Throwable $e) {
            return $this->errorResponse("Error creating post", 500);
        }
    }

    /**
     * Return a Post by ID
     * @unauthenticated
     */
    public function find(Post $post)
    {
        return $this->successResponse(new PostResource($post), 200);
    }

    /**
     * Update a Post
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        try {
            $validated = $request->validated();

            $changes = array_filter($validated, function ($value, $key) use ($post) {
                return $post->$key !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (empty($changes)) {
                return $this->errorResponse("No changes detected", 422);
            }

            $post->update($changes);

            return $this->successResponse(["updated" => array_keys($changes), "post" => new PostResource($post)], 200);

        } catch (Throwable $e) {
            return $this->errorResponse("Error updating post", 500);
        }
    }

    /**
     * Delete a Post
     */
    public function destroy(Post $post)
    {
        try {
            $post->delete();
            return $this->successResponse(["message" => "Post deleted", new PostResource($post)], 200);
        } catch (Throwable $e) {
            return $this->errorResponse("Error deleting post", 500);
        }
    }

    public function vote(Request $request, $id) {
        $action = $request->input("action");
        $userId = auth()->id();
        $likeKey = "post:{$id}:likes";
        $dislikeKey = "post:{$id}:dislikes";
        if($action==="like") {
            Redis::srem($dislikeKey, $userId);
            Redis::sadd($likeKey, $userId);
            Redis::zincrby("posts_ranked_by_likes", 1, $id);
        } elseif($action ==="dislike") {
            Redis::srem($likeKey, $userId);
            Redis::sadd($dislikeKey, $userId);
            Redis::zincrby("posts_ranked_by_likes", -1, $id);
        } else {
            return response()->json(['error' => 'invalid action']);
        }
        $likesCount = Redis::scard($likeKey);
        $dislikesCount = Redis::scard($dislikeKey);
        return response()->json([
            'likes' => $likesCount,
            'dislikes' => $dislikesCount
        ]);
    }

    public function getVoteStatus($id) {
        $userId = auth()->id();
        if(!$userId) {
            return response()->json(['like'=>false, 'dislike'=>false]);
        }

        $likeKey = "post:{$id}:likes";
        $dislikeKey = "post:{$id}:dislikes";

        $likeCount = Redis::scard($likeKey);
        $dislikeCount = Redis::scard($dislikeKey);
        $likeStatus = Redis::sismember($likeKey, $userId);
        $dislikeStatus = Redis::sismember($dislikeKey, $userId);

        return response()->json([
            'like' => $likeStatus,
            "dislike" => $dislikeStatus,
            "likes"=> $likeCount,
            "dislikes" =>$dislikeCount
        ]);

    }


}
