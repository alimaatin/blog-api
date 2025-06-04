<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->string("content");
            $table->string("thumbnail");
            $table->foreignId("category_id")->nullable()->constrained("categories")->default(1);
            $table->foreignId("user_id")->constrained("users");
            $table->enum("state", ["pending", "accepted", "rejected"])->default("pending");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
