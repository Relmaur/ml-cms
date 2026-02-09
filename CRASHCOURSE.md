# ML-CMS Framework — Crash Course

A hands-on guide to building web applications with ML-CMS. This walks you through every core concept with real examples from the framework itself.

---

## Table of Contents

1. [How It All Starts](#1-how-it-all-starts)
2. [Routing](#2-routing)
3. [Controllers](#3-controllers)
4. [Views & Layouts](#4-views--layouts)
5. [The Database Layer](#5-the-database-layer)
6. [Models](#6-models)
7. [Migrations](#7-migrations)
8. [Validation](#8-validation)
9. [Sessions & Authentication](#9-sessions--authentication)
10. [The Service Container](#10-the-service-container)
11. [Events & Listeners](#11-events--listeners)
12. [Mailer](#12-mailer)
13. [Caching](#13-caching)
14. [HTTP Responses](#14-http-responses)
15. [API Controllers](#15-api-controllers)
16. [CLI Tools](#16-cli-tools)
17. [Testing](#17-testing)
18. [Project Structure Reference](#18-project-structure-reference)

---

## 1. How It All Starts

Every request enters through a single file: `public/index.php`. This is the **front controller** pattern — one entry point handles all traffic.

```
Browser request → public/index.php → bootstrap → routes → router → controller → response
```

Here's what happens step by step:

```php
// public/index.php

require_once '../vendor/autoload.php';        // 1. Composer autoloader
$container = require_once '../bootstrap.php';  // 2. Build the DI container
Session::start();                              // 3. Start the session
require_once '../routes/web.php';              // 4. Register web routes
require_once '../routes/api.php';              // 5. Register API routes
$router = new Router($container);              // 6. Create the router
$response = $router->dispatch();               // 7. Match URL → call controller
$response->send();                             // 8. Send HTTP response
```

The `bootstrap.php` file is the **composition root** — it loads your `.env` file, creates the service container, and wires up all your dependencies (controllers, models, services). More on this in [The Service Container](#10-the-service-container).

---

## 2. Routing

Routes are defined in `routes/web.php` and `routes/api.php` using a clean, static syntax:

```php
// routes/web.php
use Core\Route;
use App\Controllers\PostsController;

Route::get('/', [PagesController::class, 'home']);
Route::get('/posts', [PostsController::class, 'index']);
Route::get('/posts/{id}', [PostsController::class, 'show']);
Route::post('/posts', [PostsController::class, 'store']);
Route::post('/posts/{id}/delete', [PostsController::class, 'destroy']);
```

### Route Parameters

Curly braces `{id}` define dynamic segments. These get passed as arguments to your controller method:

```php
// Route: /posts/{id}
// URL:   /posts/42

public function show($id)
{
    // $id === "42"
}
```

### Available HTTP Methods

```php
Route::get($uri, $action);   // GET requests
Route::post($uri, $action);  // POST requests
```

### How Matching Works

The `Router` class converts your route patterns into regex. When a request comes in, it loops through all registered routes, compares the HTTP method and URI, and on a match, resolves the controller from the container and calls the method.

```
Route::get('/posts/{id}', [PostsController::class, 'show'])
                ↓
Regex: #^/posts/([^/]+)/?$#
                ↓
URL /posts/42 matches → PostsController::show("42")
```

---

## 3. Controllers

Controllers live in `app/Controllers/` and handle the logic for each route.

### Creating a Controller

Use the CLI generator:

```bash
composer make:controller ArticlesController
```

This creates `app/Controllers/ArticlesController.php` from the stub template.

### Anatomy of a Controller

```php
namespace App\Controllers;

use Core\View;
use Core\Http\RedirectResponse;
use App\Models\Post;

class PostsController extends BaseController
{
    private Post $post;

    // Dependencies are injected via the constructor
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    // List all posts
    public function index()
    {
        $posts = $this->post->getAllPosts();

        return View::render('posts/index', [
            'pageTitle' => 'All Posts',
            'posts' => $posts
        ]);
    }

    // Show a single post
    public function show($id)
    {
        $post = $this->post->getPostById($id);

        return View::render('posts/show', [
            'pageTitle' => $post->title,
            'post' => $post
        ]);
    }

    // Handle form submission
    public function store()
    {
        // Access form data directly from $_POST
        $title = $_POST['title'];
        $content = $_POST['content'];

        $this->post->createPost($title, $content);

        return new RedirectResponse('/posts');
    }
}
```

### Protecting Routes with Auth

Extend `BaseController` and call `requireAuth()` at the top of any method that needs authentication:

```php
class DashboardController extends BaseController
{
    public function index()
    {
        $this->requireAuth(); // Redirects to /login if not authenticated

        return View::render('dashboard/index', [
            'pageTitle' => 'Dashboard'
        ], 'dashboard'); // Uses the 'dashboard' layout
    }
}
```

---

## 4. Views & Layouts

Views are plain PHP files in `app/Views/`. The framework uses output buffering to compose views within layouts.

### Rendering a View

From a controller:

```php
return View::render('posts/index', [
    'pageTitle' => 'All Posts',
    'posts' => $posts
]);
```

This renders `app/Views/posts/index.php`, wraps it in `app/Views/layouts/main.php`, and returns an `HtmlResponse`.

### The View File

```php
<!-- app/Views/posts/index.php -->
<h1>All Posts</h1>

<?php foreach ($posts as $post): ?>
    <article>
        <h2><?= htmlspecialchars($post->title) ?></h2>
        <p><?= htmlspecialchars($post->excerpt) ?></p>
        <a href="/posts/<?= $post->id ?>">Read more</a>
    </article>
<?php endforeach; ?>
```

Variables passed in the array (`$posts`, `$pageTitle`) are automatically available in the view via `extract()`.

### Layouts

Layouts are full HTML documents that wrap your view content. The view's output is injected via the `$content` variable:

```php
<!-- app/Views/layouts/main.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?? 'ML-CMS' ?></title>
</head>
<body>
    <?php require_once __DIR__ . '/../partials/header.php'; ?>

    <main>
        <?= $content ?>  <!-- Your view content goes here -->
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
```

### Using a Different Layout

Pass the layout name as the third argument:

```php
return View::render('dashboard/index', $data, 'dashboard');
// Uses app/Views/layouts/dashboard.php instead of main.php
```

### Partials

Shared UI pieces (header, footer, nav) live in `app/Views/partials/` and are included with `require_once` inside layouts.

---

## 5. The Database Layer

The framework uses SQLite with a singleton PDO wrapper that supports method chaining.

### Configuration

```php
// config/database.php
return [
    'driver' => 'sqlite',
    'path' => __DIR__ . '/../database/database.sqlite',
];
```

### Using the Database Directly

```php
use Core\Database;

$db = Database::getInstance();

// SELECT with parameter binding
$db->query("SELECT * FROM posts WHERE id = :id");
$db->bind(':id', $id);
$post = $db->fetch();       // Single row (stdClass object)

// SELECT multiple
$db->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $db->fetchAll();   // Array of stdClass objects

// INSERT
$db->query("INSERT INTO posts (title, content) VALUES (:title, :content)");
$db->bind(':title', $title);
$db->bind(':content', $content);
$db->execute();
```

### Method Chaining

The `query()`, `bind()`, and `execute()` methods all return `$this`, so you can chain:

```php
$db->query("SELECT * FROM users WHERE email = :email")
   ->bind(':email', $email)
   ->fetch();
```

### Testing

When `APP_ENV=testing`, the database automatically uses an in-memory SQLite instance — no cleanup needed.

---

## 6. Models

Models encapsulate all database queries for a specific entity. They live in `app/Models/`.

### Creating a Model

```bash
composer make:model Article
```

### Anatomy of a Model

```php
namespace App\Models;

use Core\Database;

class Post
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllPosts()
    {
        $this->db->query("
            SELECT posts.*, users.name as author_name
            FROM posts
            JOIN users ON posts.author_id = users.id
            ORDER BY posts.created_at DESC
        ");
        return $this->db->fetchAll();
    }

    public function getPostById($id)
    {
        $this->db->query("SELECT * FROM posts WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    public function createPost($title, $content, $authorId)
    {
        $this->db->query("
            INSERT INTO posts (title, content, author_id, created_at)
            VALUES (:title, :content, :author_id, :created_at)
        ");
        $this->db->bind(':title', $title);
        $this->db->bind(':content', $content);
        $this->db->bind(':author_id', $authorId);
        $this->db->bind(':created_at', date('Y-m-d H:i:s'));
        $this->db->execute();
    }
}
```

Models return `stdClass` objects (from `PDO::FETCH_OBJ`), so you access properties with arrow syntax: `$post->title`, `$user->email`.

---

## 7. Migrations

Migrations let you version-control your database schema.

### Creating a Migration

```bash
composer make:migration CreateArticlesTable
```

This generates a timestamped file in `database/migrations/`:

```php
// database/migrations/2025_01_15_143022_CreateArticlesTable.php

use Core\Database;
use Core\Interfaces\MigrationInterface;

class CreateArticlesTable implements MigrationInterface
{
    public function up(): void
    {
        $db = Database::getInstance();
        $db->query("
            CREATE TABLE IF NOT EXISTS articles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ")->execute();
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS articles")->execute();
    }
}
```

### Running Migrations

```bash
# First time only — creates the migrations tracking table
php database/migrations_setup.php

# Run all pending migrations
composer migrate

# Rollback the last batch
composer migrate:rollback
```

Migrations are tracked in a `migrations` table. Each run is assigned a batch number, and rollbacks undo the most recent batch.

---

## 8. Validation

The `Validator` class provides form validation with a clean rule syntax.

```php
use Core\Validator;

public function store()
{
    $validator = new Validator($_POST);

    $validator->validate([
        'name'     => ['required', 'min:3', 'max:255'],
        'email'    => ['required', 'email'],
        'password' => ['required', 'min:8'],
    ]);

    if ($validator->fails()) {
        $errors = $validator->errors();
        // $errors = ['name' => ['Name must be...'], 'email' => [...]]

        return View::render('users/create', [
            'errors' => $errors,
            'old' => $_POST,
        ]);
    }

    // Validation passed — proceed
}
```

### Available Rules

| Rule | Description |
|------|-------------|
| `required` | Field must be present and non-empty |
| `email` | Must be a valid email format |
| `min:N` | Minimum string length of N characters |
| `max:N` | Maximum string length of N characters |

Rules use colon syntax for parameters: `min:8` means "at least 8 characters".

---

## 9. Sessions & Authentication

### Session Basics

The `Session` class provides static methods for session management:

```php
use Core\Session;

Session::set('key', 'value');
$value = Session::get('key');
Session::has('key');            // true/false
Session::remove('key');
Session::destroy();             // End session entirely
```

### Flash Messages

Flash messages persist for exactly one read — perfect for success/error notifications after redirects:

```php
// Set a flash message (usually before a redirect)
Session::flash('success', 'Post created successfully!');

// Read it (usually in a layout/view) — auto-deleted after reading
$message = Session::getFlash('success');
```

### Authentication

The framework uses session-based authentication:

```php
// Check if user is logged in
Session::isAuthenticated(); // Checks for 'user_id' in session

// Log a user in (after password verification)
if (password_verify($_POST['password'], $user->password)) {
    Session::set('user_id', $user->id);
    Session::set('user_name', $user->name);
}

// Log out
Session::destroy();
```

### Protecting Controller Methods

```php
class DashboardController extends BaseController
{
    public function index()
    {
        $this->requireAuth(); // Redirects to /login if not authenticated
        // ... rest of the method
    }
}
```

---

## 10. The Service Container

The container is a simple dependency injection system. You register how to create things, and the framework resolves them when needed.

### Registering Bindings

All bindings are defined in `bootstrap.php`:

```php
// Simple binding — factory closure creates a new instance each time
$container->bind(Post::class, fn() => new Post());
$container->bind(User::class, fn() => new User());
$container->bind(Mailer::class, fn() => new Mailer());

// Controller with dependencies — the container resolves them
$container->bind(PostsController::class, function () use ($container) {
    return new PostsController(
        $container->resolve(Post::class)
    );
});

$container->bind(UsersController::class, function () use ($container) {
    return new UsersController(
        $container->resolve(User::class),
        $container->resolve(EventDispatcher::class)
    );
});
```

### Resolving Dependencies

```php
$post = $container->resolve(Post::class);               // New Post instance
$controller = $container->resolve(PostsController::class); // Controller with Post injected
```

### How It Connects to Routing

When the router matches a URL to a route like `[PostsController::class, 'show']`, it uses the container to resolve the controller. This is how controllers get their dependencies automatically.

### Adding a New Controller to the Container

When you create a new controller, register it in `bootstrap.php`:

```php
$container->bind(ArticlesController::class, function () use ($container) {
    return new ArticlesController(
        $container->resolve(Article::class)
    );
});
```

---

## 11. Events & Listeners

The event system lets you decouple actions. When something happens (an event), interested listeners respond to it.

### Defining an Event

Events are simple data-holding classes in `app/Events/`:

```php
// app/Events/UserRegistered.php
namespace App\Events;

class UserRegistered
{
    public $user;

    public function __construct(object $user)
    {
        $this->user = $user;
    }
}
```

### Defining a Listener

Listeners live in `app/Listeners/` and implement a `handle()` method:

```php
// app/Listeners/SendWelcomeEmailListener.php
namespace App\Listeners;

use App\Events\UserRegistered;
use Core\Mailer;

class SendWelcomeEmailListener
{
    private Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(UserRegistered $event)
    {
        $this->mailer->send(
            $event->user->email,
            'Welcome!',
            "<h1>Welcome, {$event->user->name}!</h1>"
        );
    }
}
```

### Registering Events and Listeners

In `bootstrap.php`:

```php
$dispatcher = new EventDispatcher($container);
$dispatcher->listen(UserRegistered::class, SendWelcomeEmailListener::class);

$container->bind(EventDispatcher::class, fn() => $dispatcher);
```

### Dispatching an Event

```php
$this->dispatcher->dispatch(new UserRegistered($newUser));
```

The dispatcher resolves the listener from the container (so its dependencies are injected), then calls `handle()`.

---

## 12. Mailer

The framework wraps Symfony Mailer for sending emails.

### Configuration

Set your mail transport DSN in `.env`:

```env
MAILER_DSN="smtp://user:pass@smtp.mailtrap.io:2525"
```

### Sending an Email

```php
use Core\Mailer;

$mailer = new Mailer();
$mailer->send(
    'user@example.com',        // To
    'Welcome to ML-CMS',       // Subject
    '<h1>Hello!</h1>'          // HTML body
);
```

The "from" address defaults to `no-reply@ml-cms.com`.

---

## 13. Caching

File-based caching in `storage/cache/`. Useful for expensive queries.

```php
use Core\Cache;

// Try cache first, fall back to database
$posts = Cache::get('posts.all');

if (!$posts) {
    $posts = $this->post->getAllPosts();
    Cache::put('posts.all', $posts, 10); // Cache for 10 minutes
}

// Invalidate cache when data changes
Cache::forget('posts.all');
```

Cache files are stored as serialized PHP in `storage/cache/` with MD5-hashed filenames.

---

## 14. HTTP Responses

Controllers return `Response` objects that the framework sends to the browser.

### HTML Response (via View)

```php
return View::render('posts/index', ['posts' => $posts]);
// Returns an HtmlResponse with Content-Type: text/html
```

### Redirect Response

```php
use Core\Http\RedirectResponse;

return new RedirectResponse('/posts');
// Sends a 302 redirect
```

### Custom Response

```php
use Core\Http\Response;

return new Response('Plain text content', 200, [
    'Content-Type' => 'text/plain'
]);
```

### Response Hierarchy

```
Response (base)
├── HtmlResponse    — Sets Content-Type: text/html
└── RedirectResponse — Sets Location header, 302 status
```

---

## 15. API Controllers

API controllers extend `ApiController` and return JSON responses.

```php
// app/Controllers/Api/PostApiController.php
namespace App\Controllers\Api;

use App\Models\Post;

class PostApiController extends ApiController
{
    private Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function index()
    {
        $posts = $this->post->getAllPosts();
        $this->jsonResponse(['data' => $posts]);
    }

    public function show($id)
    {
        $post = $this->post->getPostById($id);

        if (!$post) {
            $this->jsonResponse(['error' => 'Post not found'], 404);
        }

        $this->jsonResponse(['data' => $post]);
    }
}
```

### API Routes

```php
// routes/api.php
Route::get('/api/posts', [PostApiController::class, 'index']);
Route::get('/api/posts/{id}', [PostApiController::class, 'show']);
```

The `jsonResponse()` method handles setting `Content-Type: application/json` and encoding the data.

---

## 16. CLI Tools

The framework includes generators and database tools, all invokable via Composer scripts.

### Generators

```bash
# Generate a new controller
composer make:controller ProductsController

# Generate a new model
composer make:model Product

# Generate a new migration
composer make:migration CreateProductsTable
```

### Database Commands

```bash
# Set up the migrations table (first time only)
php database/migrations_setup.php

# Run pending migrations
composer migrate

# Rollback the last batch of migrations
composer migrate:rollback

# Backup the SQLite database
composer db:backup
```

### Testing

```bash
# Run the test suite
composer test
```

---

## 17. Testing

Tests use PHPUnit and live in the `tests/` directory.

### Unit Tests

For testing individual classes in isolation:

```php
// tests/Unit/ValidatorTest.php
use Core\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function test_required_rule_fails_on_empty()
    {
        $validator = new Validator(['name' => '']);
        $validator->validate(['name' => ['required']]);

        $this->assertTrue($validator->fails());
    }
}
```

### Feature Tests

Feature tests bootstrap the full application with an in-memory database:

```php
// tests/Feature/RegistrationTest.php
class RegistrationTest extends BaseFeatureTestCase
{
    public function test_user_can_register()
    {
        $_POST = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securepassword'
        ];

        $controller = $this->container->resolve(UsersController::class);
        $controller->store();

        // Assert user exists in the in-memory database
        $db = Database::getInstance();
        $db->query("SELECT * FROM users WHERE email = :email");
        $db->bind(':email', 'john@example.com');
        $user = $db->fetch();

        $this->assertNotFalse($user);
        $this->assertEquals('John Doe', $user->name);
    }
}
```

The `BaseFeatureTestCase` automatically:
- Loads the container from `bootstrap.php`
- Runs all migrations on an in-memory SQLite database
- Cleans up after each test

### Running Tests

```bash
composer test
```

---

## 18. Project Structure Reference

```
ml-cms/
├── public/
│   └── index.php              # Entry point (front controller)
├── bootstrap.php              # Container setup & dependency wiring
│
├── routes/
│   ├── web.php                # Web routes (HTML)
│   └── api.php                # API routes (JSON)
│
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php # Auth helper (requireAuth)
│   │   └── Api/
│   │       └── ApiController.php  # JSON response helper
│   ├── Models/                # Database query classes
│   ├── Views/
│   │   ├── layouts/           # HTML wrappers (main, dashboard)
│   │   ├── partials/          # Reusable UI fragments
│   │   ├── pages/             # Static page views
│   │   ├── posts/             # Post CRUD views
│   │   └── errors/            # Error pages (404)
│   ├── Events/                # Event data classes
│   └── Listeners/             # Event handlers
│
├── core/                      # Framework internals
│   ├── Container.php          # Dependency injection
│   ├── Router.php             # URL dispatching
│   ├── Route.php              # Route registration
│   ├── View.php               # Template rendering
│   ├── Database.php           # PDO wrapper (SQLite)
│   ├── Session.php            # Session & flash messages
│   ├── Validator.php          # Form validation
│   ├── Cache.php              # File-based caching
│   ├── Console.php            # CLI output helpers
│   ├── EventDispatcher.php    # Event system
│   ├── Mailer.php             # Email sending
│   ├── Http/
│   │   ├── Response.php       # Base response
│   │   ├── HtmlResponse.php   # HTML response
│   │   └── RedirectResponse.php
│   └── Interfaces/
│       └── MigrationInterface.php
│
├── config/
│   └── database.php           # Database configuration
│
├── database/
│   ├── database.sqlite        # SQLite database file
│   ├── migrations/            # Migration files
│   └── migrations_setup.php   # Bootstrap migrations table
│
├── bin/                       # CLI scripts
│   ├── migrate.php
│   ├── migrate-rollback.php
│   ├── make-migration.php
│   ├── make-controller.php
│   └── make-model.php
│
├── stubs/                     # Code generation templates
│   ├── controller.stub
│   └── model.stub
│
├── storage/
│   ├── cache/                 # File cache storage
│   └── logs/                  # Application logs
│
├── tests/
│   ├── Unit/                  # Isolated class tests
│   └── Feature/               # Full-stack integration tests
│
├── .env                       # Environment variables
├── composer.json
└── phpunit.xml
```

---

## Quick Start Checklist

1. Clone the repo and run `composer install`
2. Copy `.env.example` to `.env` and configure your `MAILER_DSN`
3. Run `php database/migrations_setup.php` to initialize migrations
4. Run `composer migrate` to create all tables
5. Point your web server (or Laravel Herd) to the `public/` directory
6. Visit `http://your-domain/` — you're live
