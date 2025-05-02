<?php

namespace App\Http\Actions\Posts;

use App\Models\Post;
use App\Models\User;

class QueryPostsAction
{
    public function execute(array $filters)
    {
        $query = Post::query();

        if (!empty($filters['user'])) {
            $user = User::where('username', $filters['user'])->first();
            if ($user) {
                $query->where('user_id', $user->id);
            }
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        $sortOrder = ($filters['sort'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortOrder);

        return $query;
    }
}