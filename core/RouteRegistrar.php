<?php

declare(strict_types=1);

namespace Core;

/**
 * Route Registrar
 * 
 * Provides a fluent interface for adding middleware to routes.
 * 
 * Example:
 * Route::get('/dashboard', [Controller::class, 'index])
 *  ->middleware(AuthMiddleWare::class)
 *  ->middeware(AdminMiddleware::class);
 */
class RouteRegistrar
{
    protected $routeIndex;

    public function __construct($routeIndex)
    {
        $this->routeIndex = $routeIndex;
    }

    /**
     * Add middleware to this route
     * 
     * @param string|array $middleware Middleware class name(s)
     * @return $this
     */
    public function middleware($middleware)
    {
        Route::addMiddlewareToRoute($this->routeIndex, $middleware);
        return $this;
    }

    /**
     * Assign a name to tihs route
     * 
     * Named routes allow you to generate URLs without hardcoding paths.
     * 
     * Convention: Use dot notation with resource name and action
     * - posts.index -> GET /posts
     * - posts.create -> GET /posts/create
     * - posts.store -> POST /posts
     * - posts.show -> GET /posts/{id}
     * - posts.edit -> GET /posts/{id}/edit
     * - posts.update -> PUT /posts/{id}
     * - posts.destroy -> DELETE /posts/{id}
     * 
     * Example: Route::get('/posts/{id}', [PostsController::class, 'show'])
     *  ->name('posts.show');
     * 
     * Then in views/controllers:
     * route('posts.show', ['id' => 5]) // Returns: /posts/5
     * 
     * @param string $name The unique name for this route
     * @return $this For method chaining
     */
    public function name($name)
    {
        Route::addNameToRoute($this->routeIndex, $name);
        return $this;
    }
}