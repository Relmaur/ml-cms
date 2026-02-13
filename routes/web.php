<?php

declare(strict_types=1);

use Core\Route;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Controllers\PagesController;
use App\Controllers\PostsController;
use App\Controllers\UsersController;
use App\Controllers\DashboardController;

Route::group(['middleware' => [CsrfMiddleware::class]], function () {

    // Homepage
    Route::get('/', [PagesController::class, 'home']);

    // Posts - RESTful routes

    // GET /posts -> Show all posts (index)
    Route::get('/posts', [PostsController::class, 'index']);

    // GET /posts/create -> Show form to create new post (require authentication)
    Route::get('/posts/create', [PostsController::class, 'create'])->middleware(AuthMiddleware::class);

    // POST /posts -> Store new post in database
    Route::post('/posts', [PostsController::class, 'store'])->middleware(AuthMiddleware::class);

    // GET /posts/{id} -> Show single post
    // This must come after /posts/create so 'create' isn't matched as {id}
    Route::get('/posts/{id}', [PostsController::class, 'show']);

    // GET /posts/{id}/edit -> Show form to edit post
    Route::get('/posts/{id}/edit', [PostsController::class, 'edit'])->middleware(AuthMiddleware::class);

    // PUT /posts/{id} -> Update post in database (replaces old post data)
    Route::put('/posts/{id}', [PostsController::class, 'update'])->middleware(AuthMiddleware::class); // Done ✅: Momentarily we'll use POST to emulate PUT and PATCH

    // DELETE /posts/{id} -> Delete post from database
    Route::delete('/posts/{id}', [PostsController::class, 'destroy'])->middleware(AuthMiddleware::class);

    // Users - Guest only (redirect to dashboard if already logged in)
    Route::get('/register', [UsersController::class, 'register'])->middleware(GuestMiddleware::class);
    Route::post('/register', [UsersController::class, 'store'])->middleware(GuestMiddleware::class);
    // Route::get('/login', [UsersController::class, 'login'])->middleware(GuestMiddleware::class);
    // Route::post('/login', [UsersController::class, 'authenticate'])->middleware(GuestMiddleware::class);
    Route::group(['middleware' => [GuestMiddleware::class]], function () {
        Route::get('/login', [UsersController::class, 'login'])->middleware(GuestMiddleware::class);
        Route::post('/login', [UsersController::class, 'authenticate'])->middleware(GuestMiddleware::class);
    });

    // Logout (requres auth)
    Route::get('/logout', [UsersController::class, 'logout'])->middleware(AuthMiddleware::class);

    // Pages
    Route::get('/home', [PagesController::class, 'home']);
    Route::get('/about', [PagesController::class, 'about']);

    // Dashboard - requires authentication
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(AuthMiddleware::class);
});

/** Testing */
// Route::get('/debug-routes', function () {
//     echo '<pre>';
//     foreach (Core\Route::getRoutes() as $route) {
//         echo $route['method'] . ' ' . $route['uri'] . "\n";
//     }
//     echo '</pre>';
//     exit;
// });

/** Examples */

// Simple hello world closure
Route::get('/hello', function ($request) {
    return "Hello from a closure route!";
});

// Closure that uses route parameters
Route::get('/greet/{name}', function ($request, $name) {
    return "Hello, " . e($name) . "!";
});

// API endpoint that returns JSON
Route::get('/api/status', function ($request) {
    return [
        'status' => 'online',
        'version' => '1.0.0',
        'timestamp' => date('Y-m-d H:i:s')
    ];
});

/** Debug Routes */
// Debug route to see all registered routes
Route::get('/debug-routes', function ($request) {
    $output = '<h1>Registered Routes</h1>';
    $output .= '<table border="1" cellpadding="10" style="border-collapse: collapse;">';
    $output .= '<tr><th>Method</th><th>URI</th><th>Action</th><th>Middleware</th></tr>';

    foreach (Core\Route::getRoutes() as $route) {
        $output .= '<tr>';
        $output .= '<td><strong>' . $route['method'] . '</strong></td>';
        $output .= '<td>' . $route['uri'] . '</td>';

        // Display action type
        if ($route['action'] instanceof Closure) {
            $output .= '<td><em>Closure</em></td>';
        } else {
            $output .= '<td>' . $route['action'][0] . '@' . $route['action'][1] . '</td>';
        }

        // Display middleware
        $output .= '<td>' . implode(', ', $route['middleware']) . '</td>';
        $output .= '</tr>';
    }

    $output .= '</table>';

    return $output;
});
