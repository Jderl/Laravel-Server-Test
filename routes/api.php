<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UploadController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/users', [AuthController::class, 'getAllUsers']);
Route::middleware('auth:sanctum')->put('/users/update', [AuthController::class, 'update']);




// Route to get post content
Route::get('/posts/content', [PostController::class, 'getPostContent']);

// Route to get posts
Route::get('/posts', [PostController::class, 'getPosts']);

//get Singleposts 
Route::get('/posts/{id}', [PostController::class, 'getSinglePost']);


// Route to get popular contents
Route::get('/posts/popular', [PostController::class, 'getPopularContents']);

// Route to get a specific post
Route::get('/posts/{postId}', [PostController::class, 'getPost']);

// Route to create a new post
Route::post('/posts', [PostController::class, 'store']);
//Route::post('/posts/create-post', [PostController::class, 'createPost']); 
Route::post('/posts/create-post', [PostController::class, 'create']);


// Route to update a post
Route::put('/posts/{id}', [PostController::class, 'updatePost']);
// Route to delete a post
Route::delete('/posts/{id}', [PostController::class, 'deletePost']);

// Route to upload file 
Route::post('/upload', [UploadController::class, 'upload']);


//viewers
Route::get('/posts/{id}', [PostController::class, 'getSinglePost']);
Route::get('/users/{id}', [AuthController::class, 'getWriterInfo']);
