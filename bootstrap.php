<?php

use Symfony\Component\Dotenv\Dotenv;

use Core\Container;
use Core\EventDispatcher;
use Core\Mailer;

// Models
use App\Models\Post;
use App\Models\User;

// Controllers
use App\Controllers\PostsController;
use App\Controllers\UsersController;
use App\Controllers\DashboardController;
use App\Controllers\PagesController;
// Api
use App\Controllers\Api\PostApiController;

use App\Events\UserRegistered;
use App\Listeners\SendWelcomeEmailListener;

// Load environment variables from .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$container = new Container();
$dispatcher = new EventDispatcher($container);

// Register the dispatcher in the container so we can inject it later
$container->bind(EventDispatcher::class, fn() => $dispatcher);

/**
 * Event / Listener Bindings
 */
// You can add more listeners here for the same event:
$dispatcher->listen(UserRegistered::class, SendWelcomeEmailListener::class);
// $dispatcher->listen(UserRegistered::class, AssignDefaultRoleListener::class);

/**
 * Web Route Bindings
 */

// Bind the recipes for creating our models
$container->bind(Post::class, fn() => new Post());
$container->bind(User::class, fn() => new User());
// Bind the mailer service
$container->bind(Mailer::class, fn() => new Mailer());

// Bind controllers and inject their dependencies
$container->bind(PostsController::class, function () use ($container) {
    // Resolve the post model from the container and pass it in
    return new PostsController($container->resolve(Post::class));
});

$container->bind(UsersController::class, function () use ($container) {
    return new UsersController(
        $container->resolve(User::class),
        $container->resolve(EventDispatcher::class)
    );
});

$container->bind(DashboardController::class, function () use ($container) {
    return new DashboardController();
});

$container->bind(PagesController::class, function () use ($container) {
    return new PagesController();
});

$container->bind(PostApiController::class, function () use ($container) {
    return new PostApiController($container->resolve(Post::class));
});

$container->bind(SendWelcomeEmailListener::class, function () use ($container) {
    return new SendWelcomeEmailListener($container->resolve(Mailer::class));
});

// We can return the container to be used by other parts of the app
return $container;
