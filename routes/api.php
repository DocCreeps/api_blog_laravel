<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\FollowController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth Public
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Public Resources
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/slug/{slug}', [PostController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{tag}', [TagController::class, 'show']);
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth Me & Logout
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Posts
        Route::get('/posts/drafts', [PostController::class, 'drafts']);
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);

        // Categories
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Tags
        Route::post('/tags', [TagController::class, 'store']);
        Route::delete('/tags/{tag}', [TagController::class, 'destroy']);

        // Comments
        Route::post('/comments', [CommentController::class, 'store']);
        Route::put('/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
        Route::post('/comments/{comment}/approve', [CommentController::class, 'approve']);
        Route::post('/comments/{comment}/reject', [CommentController::class, 'reject']);

        // Interactions (Likes & Follows)
        Route::post('/posts/{post}/like', [FollowController::class, 'likePost']);
        Route::post('/categories/{category}/follow', [FollowController::class, 'followCategory']);
        Route::post('/users/{user}/follow', [FollowController::class, 'followUser']);
    });
});
