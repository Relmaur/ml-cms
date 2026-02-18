<?php

declare(strict_types=1);

use App\Controllers\DashboardController;
use App\Controllers\PagesController;
use App\Controllers\PostsController;
use App\Controllers\UsersController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use Closure;
use Core\Environment;
use Core\Route;

// Apply CSRF to all routes
Route::group(['middleware' => [CsrfMiddleware::class]], function () {

    /*
       =============
          MARK: HOME
       =============
    */
    Route::get('/', [PagesController::class, 'home'])
        ->name('home');

    /*
       ====================================================
          MARK: POSTS - RESTful routes with standard naming
       ====================================================
    */

    // GET /posts -> Show all posts (index)
    Route::get('/posts', [PostsController::class, 'index'])
        ->name('posts.index');

    // GET /posts/create -> Show form to create new post (require authentication)
    Route::get('/posts/create', [PostsController::class, 'create'])
        ->middleware(AuthMiddleware::class)
        ->name('posts.create');

    // POST /posts -> Store new post in database
    Route::post('/posts', [PostsController::class, 'store'])
        ->middleware(AuthMiddleware::class)
        ->name('posts.store');

    // GET /posts/{id} -> Show single post
    // This must come after /posts/create so 'create' isn't matched as {id}
    Route::get('/posts/{id}', [PostsController::class, 'show'])
        ->name('posts.show');

    // GET /posts/{id}/edit -> Show form to edit post
    Route::get('/posts/{id}/edit', [PostsController::class, 'edit'])
        ->middleware(AuthMiddleware::class)
        ->name('posts.edit');

    // PUT /posts/{id} -> Update post in database (replaces old post data)
    Route::put('/posts/{id}', [PostsController::class, 'update'])
        ->middleware(AuthMiddleware::class)
        ->name('posts.update'); // Done ✅: Momentarily we'll use POST to emulate PUT and PATCH

    // DELETE /posts/{id} -> Delete post from database
    Route::delete('/posts/{id}', [PostsController::class, 'destroy'])
        ->middleware(AuthMiddleware::class)
        ->name('posts.destroy');

    /*
       =======================
          MARK: Authentication
       =======================
    */
    // Users - Guest only (redirect to dashboard if already logged in)
    Route::get('/register', [UsersController::class, 'register'])
        ->middleware(GuestMiddleware::class)
        ->name('register');
    Route::post('/register', [UsersController::class, 'store'])
        ->middleware(GuestMiddleware::class)
        ->name('register.store');
    // Route::get('/login', [UsersController::class, 'login'])->middleware(GuestMiddleware::class);
    // Route::post('/login', [UsersController::class, 'authenticate'])->middleware(GuestMiddleware::class);
    Route::group(['middleware' => [GuestMiddleware::class]], function () {
        Route::get('/login', [UsersController::class, 'login'])
            ->middleware(GuestMiddleware::class)
            ->name('login');
        Route::post('/login', [UsersController::class, 'authenticate'])
            ->middleware(GuestMiddleware::class)
            ->name('login.authenticate');
    });

    // Logout (requres auth)
    Route::get('/logout', [UsersController::class, 'logout'])
        ->middleware(AuthMiddleware::class)
        ->name('logout');

    /*
       ==============
          MARK: PAGES
       ==============
    */
    Route::get('/home', [PagesController::class, 'home'])
        ->name('pages.home');
    Route::get('/about', [PagesController::class, 'about'])
        ->name('pages.about');

    /*
       ==================
          MARK: DASHBOARD
       ==================
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(AuthMiddleware::class)
        ->name('dashboard');
});

/*
   ============================================
      MARK: DEBUG ROUTES (remove in production)
   ============================================
*/
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
})
    ->name('debug.routes');

/*
   ==============
   MARK: Examples
   ==============
*/

/*
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

// Protected closure route (requires authentication)
Route::get('/admin/quick-stats', function ($request) {
    return '<h1>Quick Stats</h1><p>Total users: 42</p>';
})->middleware(AuthMiddleware::class);

// Redirect Route (Bonus)
//Simple redirect using a closure
Route::get('/old-page', function ($request) {
    return new \Core\Http\RedirectResponse('/new-page');
});
*/

/*
// In routes/web.php
Route::get('/test-query-builder', function ($request) {
    $db = Core\Database::getInstance();

    // var_dump($request);

    echo '<h1>Query Builder Tests</h1>';

    // Test 1: Get all posts
    echo '<h2>All Posts</h2>';
    $posts = $db->table('posts')->get();
    echo '<pre>' . print_r($posts, true) . '</pre>';

    // Test 2: Find by ID
    echo '<h2>Post ID 1</h2>';
    $post = $db->table('posts')->find(1);
    echo '<pre>' . print_r($post, true) . '</pre>';

    // Test 3: Where clause
    echo '<h2>Posts with WHERE</h2>';
    $posts = $db->table('posts')
        ->where('author_id', 1)
        ->get();
    echo '<pre>' . print_r($posts, true) . '</pre>';

    // Test 4: Order and limit
    echo '<h2>Latest 3 Posts</h2>';
    $posts = $db->table('posts')
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
    echo '<pre>' . print_r($posts, true) . '</pre>';

    // Test 5: JOIN
    echo '<h2>Posts with Author Names (JOIN)</h2>';
    $posts = $db->table('posts')
        ->leftJoin('users', 'posts.author_id', '=', 'users.id')
        ->select(['posts.*', 'users.name as author_name'])
        ->get();
    echo '<pre>' . print_r($posts, true) . '</pre>';

    // Test 6: Count
    echo '<h2>Total Posts</h2>';
    $count = $db->table('posts')->count();
    echo '<p>Total: ' . $count . '</p>';

    exit;
});

// In routes/web.php
Route::get('/test-model', function ($request) {
    echo '<h1>Testing Base Model</h1>';

    // Test 1: Get all posts (static method)
    echo '<h2>All Posts (Post::all())</h2>';
    $posts = App\Models\Post::all();
    echo '<pre>' . print_r($posts, true) . '</pre>';

    // Test 2: Find by ID
    echo '<h2>Find Post 1 (Post::find(1))</h2>';
    $post = App\Models\Post::find(1);
    echo '<pre>' . print_r($post, true) . '</pre>';

    // Test 3: Magic properties
    echo '<h2>Magic Properties ($post->title)</h2>';
    $content = $post->content ? substr($post->content, 0, 100) . '...' : 'No content';
    if ($post) {
        echo '<p>Title: ' . $post->title . '</p>';
        echo '<p>Content: ' . $content . '...</p>';
        echo '<p>Created: ' . $post->created_at . '</p>';
    }

    // Test 4: Query builder access
    echo '<h2>Query Builder (Post::where())</h2>';
    $posts = App\Models\Post::where('author_id', 1)
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
    echo '<p>Found ' . count($posts) . ' posts by author 1</p>';

    // Test 5: Create new post
    echo '<h2>Create New Post (Post::create())</h2>';
    $newPost = App\Models\Post::create([
        'title' => 'Test Post from Model',
        'content' => 'This was created using the ORM!',
        'author_id' => 1
    ]);
    echo '<p>Created post with ID: ' . $newPost->id . '</p>';
    echo '<p>Created at: ' . $newPost->created_at . '</p>';

    // Test 6: Update the post
    echo '<h2>Update Post ($post->save())</h2>';
    $newPost->title = 'Updated Title';
    $newPost->save();
    echo '<p>Updated! Only "title" and "updated_at" were sent to database (dirty tracking)</p>';

    // Test 7: Delete the post
    echo '<h2>Delete Post ($post->delete())</h2>';
    $newPost->delete();
    echo '<p>Deleted!</p>';

    // Test 8: Custom methods
    echo '<h2>Custom Methods (Post::latest())</h2>';
    $latest = App\Models\Post::latest(5);
    echo '<p>Latest 5 posts:</p>';
    foreach ($latest as $p) {
        echo '<p>- ' . $p->title . '</p>';
    }

    exit;
});

Route::get('/test-env', function ($request) {
    echo '<h1>Environment Configuration Test</h1>';

    echo '<h2>Environment Info</h2>';
    echo '<p>APP_ENV: ' . env('APP_ENV') . '</p>';
    echo '<p>APP_DEBUG: ' . (env('APP_DEBUG') ? 'true' : 'false') . '</p>';
    echo '<p>APP_URL: ' . env('APP_URL') . '</p>';

    echo '<h2>Database Info</h2>';
    echo '<p>DB_CONNECTION: ' . env('DB_CONNECTION') . '</p>';

    if (env('DB_CONNECTION') === 'sqlite') {
        echo '<p>DB_PATH: ' . env('DB_PATH') . '</p>';
    } else {
        echo '<p>DB_HOST: ' . env('DB_HOST') . '</p>';
        echo '<p>DB_DATABASE: ' . env('DB_DATABASE') . '</p>';
    }

    $db = Core\Database::getInstance();
    echo '<p>Active Driver: ' . $db->getDriver() . '</p>';

    echo '<h2>Environment Helpers</h2>';
    echo '<p>Is Local: ' . (Environment::isLocal() ? 'Yes' : 'No') . '</p>';
    echo '<p>Is Production: ' . (Environment::isProduction() ? 'Yes' : 'No') . '</p>';
    echo '<p>Is Debug: ' . (Environment::isLocal() ? 'Yes' : 'No') . '</p>';

    exit;
});
*/