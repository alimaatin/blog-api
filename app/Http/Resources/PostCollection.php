<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'displayName' => $this->user->displayName,
            ],
            'title' => $this->title,
            'thumbnail' => $this-> thumbnail,
            'category' => $this->category->name,
            'state' => $this->state,
            'likes' => $this->likes,
            'dislikes' => $this->dislikes,
            'created_at' => $this->created_at,
            'updated_at'=> $this->updated_at,
        ];
    }
}
