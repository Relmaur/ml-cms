<?php

namespace Core;

use Core\Route;
use Core\View;

class Router
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach (Route::getRoutes() as $route) {

            // Convert URI to a regex pattern
            $pattern = $this->convertRouteToRegex($route['uri']);

            // Checks if the current request URI matches the pattern and the method is correct
            if ($route['method'] == $method && preg_match($pattern, $uri, $matches)) {

                // Remove the full match from the beginning of the array
                array_shift($matches);

                // Extract action and call it
                $action = $route['action'];
                if (is_array($action) && count($action) === 2) {
                    $controllerName = $action[0];
                    $methodName = $action[1];

                    if (class_exists($controllerName) && method_exists($controllerName, $methodName)) {

                        $controllerInstance = $this->container->resolve($controllerName);

                        call_user_func_array([$controllerInstance, $methodName], $matches);
                        return;
                    }
                }
            }
        }
        // Handle 404 Not Found
        http_response_code(404);
        View::render('errors/404', ['pageTitle' => 'Not Found']);
    }

    private function convertRouteToRegex($uri)
    {
        // Convert route placeholders like {id} to a regex capture group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $uri);
        // Escape forward slashes and add start/end anchors
        return '#^' . str_replace('/', '\/', $pattern) . '\/?$#';
    }
}
