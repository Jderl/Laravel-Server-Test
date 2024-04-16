<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/users', [AuthController::class, 'getAllUsers']);


// Route to get post content
Route::get('/posts/content', [PostController::class, 'getPostContent']);

// Route to get posts
Route::get('/posts', [PostController::class, 'getPosts']);

// Route to get popular contents
Route::get('/posts/popular', [PostController::class, 'getPopularContents']);

// Route to get a specific post
Route::get('/posts/{postId}', [PostController::class, 'getPost']);

// Route to create a new post
Route::post('/posts', [PostController::class, 'createPost']);

// Route to update a post
Route::put('/posts/{id}', [PostController::class, 'updatePost']);

// Route to delete a post
Route::delete('/posts/{id}', [PostController::class, 'deletePost']);