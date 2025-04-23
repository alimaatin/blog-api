<?php

use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsurePostIsVisible;
use App\Http\Middleware\EnsureResourceIsForUser;
use App\Http\Middleware\JWTmiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix("auth")->group(function() {
    Route::post("/login", "login");
    Route::post("/register", "register");

    Route::middleware(JWTmiddleware::class)->group(function() {
        Route::post("/logout", "logout");
        Route::get("/user", "user");
    });
});

Route::controller(AdminPostController::class)->prefix("admin/posts")->middleware(JWTmiddleware::class)->group(function() {
    Route::get("/", "index");
    Route::get("/{id}", "find");
    Route::put("/accept/{id}", "accept");
    Route::put("/reject/{id}", "reject");
});

Route::controller(PostController::class)->prefix("posts")->group(function() {
    Route::get("/", "index");
    Route::get("/{id}", "find")->middleware([EnsurePostIsVisible::class]);
    Route::post("/create", "store")->middleware([JWTmiddleware::class]);
    Route::put("/update/{id}", "update")->middleware([EnsureResourceIsForUser::class, JWTmiddleware::class]);
    Route::delete("/delete/{id}", "destroy")->middleware([EnsureResourceIsForUser::class, JWTmiddleware::class]);
    Route::put("/like/{id}", [LikeController::class, "like"])->middleware([JWTmiddleware::class]);
    Route::put("/dislike/{id}", [LikeController::class, "dislike"])->middleware([JWTmiddleware::class]);
});

Route::controller(CommentController::class)->prefix("comments")->group(function() {
    Route::get("/", "index")->middleware([AdminMiddleware::class, JWTmiddleware::class]);
    Route::get("/{postId}", "find")->middleware([EnsurePostIsVisible::class]);
    Route::post("/create", "store")->middleware([JWTmiddleware::class]);
    Route::put("/update/{id}", "update")->middleware([EnsureResourceIsForUser::class, JWTmiddleware::class]);
    Route::delete("/delete/{id}", "destroy")->middleware([EnsureResourceIsForUser::class, JWTmiddleware::class]);
});

Route::controller(CategoryController::class)->prefix("categories")->group(function() {
    Route::get("/", "index");
    Route::get("/{id}", "find");
    Route::post("/create", "store")->middleware(AdminMiddleware::class);
    Route::put("/update/{id}", "update")->middleware(AdminMiddleware::class);
    Route::delete("/delete/{id}", "destroy")->middleware(AdminMiddleware::class);
});
