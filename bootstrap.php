<?php

use Core\Container;
use App\Models\Post;
use App\Models\User;
use App\Controllers\PostsController;
use App\Controllers\UsersController;
use App\Controllers\DashboardController;
use App\Controllers\PagesController;

$container = new Container();

// Bind the recipes for creating our models
$container->bind(Post::class, fn() => new Post());
$container->bind(User::class, fn() => new User());

// Bind controllers and inject their dependencies
$container->bind(PostsController::class, function () use ($container) {
    // Resolve the post model from the container and pass it in
    return new PostsController($container->resolve(Post::class));
});
$container->bind(UsersController::class, function () use ($container) {
    return new UsersController($container->resolve(User::class));
});
$container->bind(DashboardController::class, function () use ($container) {
    return new DashboardController();
});
$container->bind(PagesController::class, function () use ($container) {
    return new PagesController();
});

// We can return the container to be used by other parts of the app
return $container;
