<?php

declare(strict_types=1);

namespace Core;

use Closure;

class Route
{
    /**
     * All registered routes
     * Each route has: method, uri, action, middleware, name
     */
    protected static $routes = [];

    /**
     * Named routes registry
     * Maps route names to their index in the $routes array
     * 
     * Example ['posts.show' => 3, 'posts.edit' => 4]
     * This allows fast lookup: "What's the URI for 'posts.show'?"
     */
    protected static $namedRoutes = [];

    /**
     * Stack of route groups (for nesting groups)
     */
    protected static $groupStack = [];

    /**
     * Register a GET route
     * 
     * GET is used to RETRIEVE data (show a page, list items, etc.)
     * Example: Route::get('/items', [ItemsController::class, 'index']);
     */
    public static function get($uri, $action): RouteRegistrar
    {
        return self::add('GET', $uri, $action);
    }

    /**
     * Register a POST route
     * 
     * POST is used to CREATE new data (submit forms, add new items, etc.)
     * Example: Route::post('/items', [ItemsController::class, 'store']);
     */
    public static function post($uri, $action): RouteRegistrar
    {
        return self::add('POST', $uri, $action);
    }

    /**
     * Register a a PUT route
     * 
     * PUT is used to REPLACE an entire resource (update ALL fields)
     * 
     * Example: Route::put('/items/{id}', [ItemsController::class, 'update']);
     * 
     * Think of PUT as "replace the whole thing"
     */
    public static function put($uri, $action): RouteRegistrar
    {
        return self::add('PUT', $uri, $action);
    }

    /**
     * Register a PATCH route
     * 
     * PATCH is used to UPDATE PART of a resource (update specific fields)
     * Example: Route::patch('/items/{id}', [ItemsController::class, 'update']);
     * 
     * Think of PATCH as "just change this one thing"
     */
    public static function patch($uri, $action): RouteRegistrar
    {
        return self::add('PATCH', $uri, $action);
    }

    /**
     * Register a DELETE route
     * 
     * DELETE is used to REMOVE a resource
     * Example: Route::delete('/items/{id}', [ItemsController::class, 'destroy']);
     */
    public static function delete($uri, $action): RouteRegistrar
    {
        return self::add('DELETE', $uri, $action);
    }

    /**
     * Register a route that responds to multiple HTTP methods
     * 
     * Useful when the same controller method handles multiple verbs
     * Example: Route::match(['GET', 'POST'], '/contact', [ContactController::class, 'handle']);
     */
    public static function match(array $methods, $uri, $action): void
    {
        foreach ($methods as $method) {
            self::add(strtoupper($method), $uri, $action);
        }
    }

    /**
     * Register a route that responds to ANY HTTP method
     * 
     * Useful for catch-all routes or debugging
     * Example: Route::any('/fallback', [FallbackController::class, 'handle']);
     */
    public static function any($uri, $action): void
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        foreach ($methods as $method) {
            self::add($method, $uri, $action);
        }
    }

    /**
     * Create a route group with shared attributes
     * 
     * Example:
     * Route::group(['middleware' => ['auth], 'prefix' => '/admin'], function() {
     *  Route::get('/dashboard', [AdminController::class, 'index']);
     * })
     */
    public static function group(array $attributes, Closure $callback): void
    {

        // Push attributes onto the group stack
        self::$groupStack[] = $attributes;

        // Execute the callback to register routes
        $callback();

        // Pop the group off the stack
        array_pop(self::$groupStack);
    }

    /**
     * Add a route and return a RouteRegistrar for chainging
     */
    protected static function add($method, $uri, $action): RouteRegistrar
    {

        // Merge group attributes
        $attributes = self::mergeGroupAttributes();

        // Apply prefix to URI
        if (isset($attributes['prefix'])) {
            $uri = trim($attributes['prefix'], '/') . '/' . trim($uri, '/');
        }

        // Build route definition
        $route = [
            'method' => $method,
            'uri' => '/' . trim($uri, '/'),
            'action' => $action,
            'middleware' => $attributes['middleware'] ?? [],
            'name' => null // Routes can have a name
        ];

        self::$routes[] = $route;

        // Return a route registrar for method chaining (e.g. ->middleware())
        return new RouteRegistrar(count(self::$routes) - 1);
    }

    /**
     * Merge attributes from the group stack
     * 
     * When routes are nested in groups, this combines all the group attributes
     */
    protected static function mergeGroupAttributes(): array
    {
        $attributes = [
            'prefix' => '',
            'middleware' => []
        ];

        foreach (self::$groupStack as $group) {
            // Merge prefixes (concatenate them)
            if (isset($group['prefix'])) {
                $attributes['prefix'] = trim($attributes['prefix'], '/') . '/' . trim($group['prefix'], '/');
            }

            // Merge middleware arrays
            if (isset($group['middleware'])) {
                $middleware = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $attributes['middleware'] = array_merge($attributes['middleware'], $middleware);
            }
        }
        return $attributes;
    }

    /**
     * Add a middleware to a specific route (called by RouteRegistrar)
     */
    public static function addMiddlewareToRoute($index, $middleware): void
    {
        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        self::$routes[$index]['middleware'] = array_merge(
            self::$routes[$index]['middleware'],
            $middleware
        );
    }

    /**
     * Add a name to a specific route (called by RoutesRegistrar)
     * 
     * When you call ->name('posts.show'), this method:
     * 1. Stores the name in the route definition
     * 2. Adds the name to the named routes registry for fast lookup
     * 
     * @param int $index The route index in the $routes array
     * @param string $name The route name
     * @throws \Exception If the name is already taken
     */
    public static function addNameToRoute($index, $name): void
    {

        // Check if name is already used
        if (isset(self::$namedRoutes[$name])) {
            throw new \Exception("Route name '{$name}' is already in use.");
        }

        // Store the name in the route definition
        self::$routes[$index]['name'] = $name;

        // Add to the named routes registry
        self::$namedRoutes[$name] = $index;
    }

    /**
     * Get a route by its name
     * 
     * This is used by the route() helper function to look up routes
     * 
     * @param string $name The route name
     * @return array|null The route definition or null if not found
     */
    public static function getByName($name): ?array
    {
        if (!isset(self::$namedRoutes[$name])) {
            return null;
        }

        $index = self::$namedRoutes[$name];
        return self::$routes[$index];
    }

    /**
     * Get all registered routes
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Get all named routes
     * 
     * Useful for debugging or generating route lists
     * 
     * @return array Array of ['name' => 'route definition']
     */
    public static function getNamedRoutes(): array
    {
        $named = [];
        foreach (self::$namedRoutes as $name => $index) {
            $named[$name] = self::$routes[$index];
        }
        return $named;
    }
}
