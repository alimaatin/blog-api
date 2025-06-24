<?php

namespace App\Repository;

use App\Models\Post;
use App\Models\User;

class PostRepository
{
    protected $model;

    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    public function getByFilters(array $filters)
    {
        $query = $this->model->query();

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

        $result = $query->get();

        return $result;
    }
}