<?php

use Core\Route;
use App\Controllers\PagesController;
use App\Controllers\PostsController;
use App\Controllers\UsersController;
use App\Controllers\DashboardController;

// Homepage
Route::get('/', [PagesController::class, 'home']);


// Posts
Route::get('/posts', [PostsController::class, 'index']);
Route::get('/posts/create', [PostsController::class, 'create']);
Route::post('/posts', [PostsController::class, 'store']);
Route::get('/posts/{id}', [PostsController::class, 'show']);
Route::get('/posts/{id}/edit', [PostsController::class, 'edit']);
Route::post('/posts/{id}', [PostsController::class, 'update']); // Momentarily we'll use POST to emulate PUT and PATCH
Route::post('/posts/{id}/delete', [PostsController::class, 'destroy']);

// Users
Route::get('/register', [UsersController::class, 'register']);
Route::post('/register', [UsersController::class, 'store']);
Route::get('/login', [UsersController::class, 'login']);
Route::post('/login', [UsersController::class, 'authenticate']);
Route::get('/logout', [UsersController::class, 'logout']);

// Pages
Route::get('/home', [PagesController::class, 'home']);
Route::get('/about', [PagesController::class, 'about']);

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index']);
