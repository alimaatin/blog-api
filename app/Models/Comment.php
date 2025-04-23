<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    use HasFactory;
    protected $fillable = [
        "post_id",
        "parent_id",
        "content",
        "user_id"
    ];

    public function parent()
    {
        return $this->belongsTo(Comment::class);
    }

    public function children()
    {
        return $this->hasMany(Comment::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
