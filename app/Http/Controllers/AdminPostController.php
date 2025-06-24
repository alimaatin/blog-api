<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Repository\PostRepository;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Throwable;

class AdminPostController extends Controller
{
    use ApiResponseTrait;

    protected $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * Return all Posts
     */
    #[QueryParameter('user', description: 'Username of the author.', type: 'string', default:'alimatin', example: 'alimatin')]
    #[QueryParameter('search', description: 'Search term for post titles', type: 'string', example: 'hello')]
    #[QueryParameter('sort', description: 'How you want to sort the content(asc, desc)', type: 'string', default: 'desc', example: 'asc')]
    #[QueryParameter('state', description: 'State of the post', type: 'string', default: 'pending', example: 'accepted')]
    public function index(Request $request)
    {
        $posts = $this->postRepository->getByFilters($request->all());
        foreach ($posts as $post) {
            $post->user;
            $post->category;
        }
        return $this->successResponse(
            PostResource::collection($posts),
            200
        );

    }

    /**
     * Return a Post by ID
     */
    public function find(Post $post)
    {
        return $this->successResponse(
            new PostResource($post->with(['user', 'category'])->get()),
            200
        );
    }

    /**
     * Accept a pending Post
     */
    public function accept(Post $post)
    {
        try {
            $post->update(['state' => 'accepted']);
            return $this->successResponse(
                new PostResource($post),
                200
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode(),
            );
        }
    }

    /**
     * Reject a pending Post
     */
    public function reject(Post $post)
    {
        try {
            $post->update(['state' => 'rejected']);
            return $this->successResponse(
                new PostResource($post),
                200
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode(),
            );
        }
    }

    /**
     * Delete a Post
     */
    public function destroy(Post $post)
    {
        try {
            $post->delete();
            return $this->successResponse(
                new PostResource($post),
                200
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode(),
            );
        }
    }
}
