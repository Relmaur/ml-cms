<?php

declare(strict_types=1);

namespace Core;

use Core\Route;
use Core\View;
use Core\Http\Request;
use Core\Http\Response;
use Closure;

class Router
{
    protected $container;
    protected $request;

    public function __construct(Container $container, Request $request = null)
    {
        $this->container = $container;
        // Use provided request or capture from superglobals
        $this->request = $request ?? Request::capture();
    }

    public function dispatch()
    {
        $uri = $this->request->path();

        /**
         * Check for method spoofing
         */
        // If there's a _method field in POST data, use that instead
        $method = $this->getRequestMethod();

        foreach (Route::getRoutes() as $route) {

            // Convert URI to a regex pattern
            $pattern = $this->convertRouteToRegex($route['uri']);

            // Checks if the current request URI matches the pattern and the method is correct
            if ($route['method'] == $method && preg_match($pattern, $uri, $matches)) {

                // Remove the full match from the beginning of the array
                array_shift($matches);

                // Extract action and call it
                $action = $route['action'];

                // Check if action is a closure
                if ($action instanceof Closure) {
                    return $this->runThroughMiddleware(

                        $route['middleware'] ?? [],

                        function ($request) use ($action, $matches) {
                            // Call the closure with the request and any route parameters
                            // For example: ROute::get('/posts/{id}', function($request, $id) { ... })
                            $result = call_user_func_array($action, array_merge([$request], $matches));

                            // If the closure returns a Response, use it directly
                            // Otherwise, wrap the output in an HTMLResponse
                            if ($result instanceof Response) {
                                return $result;
                            }

                            // If closure echoes output instead of returning, capture it
                            if ($result === null && ob_get_length() > 0) {
                                $content = ob_get_clean();
                                return new \Core\Http\HtmlResponse($content);
                            }

                            // If closure returned a string, wrap it in HtmlResponse
                            if (is_string($result)) {
                                return new \Core\Http\HtmlResponse($result);
                            }

                            // If closure returned an array, convert to JSON
                            if (is_array($result)) {
                                return new \Core\Http\JsonResponse($result);
                            }

                            // If closure returned null (just echoed output), that's okay too
                            // The output is already sent to the browser
                            return new \Core\Http\Response('', 200);
                        }
                    );
                }

                // Action is a controller array [ControllerClass::class, 'method']
                if (is_array($action) && count($action) === 2) {
                    $controllerName = $action[0];
                    $methodName = $action[1];

                    if (class_exists($controllerName) && method_exists($controllerName, $methodName)) {

                        // Run through middleware pipeline
                        return $this->runThroughMiddleware(
                            $route['middleware'] ?? [],
                            function ($request) use ($controllerName, $methodName, $matches) {
                                $controllerInstance = $this->container->resolve($controllerName);

                                // Use reflection to inject Request where type-hinted
                                // and route parameters everywhere else
                                $args = $this->resolveMethodArgs($controllerInstance, $methodName, $request, $matches);

                                return call_user_func_array(
                                    [$controllerInstance, $methodName],
                                    $args
                                );
                            }
                        );
                    }
                }
                // Controller/method not found - 404
                return View::render('errors/404', ['pageTitle' => 'Not Found']);
            }
        }

        // No route matched - 404
        return View::render('errors/404', ['pageTitle' => 'Not Found']);
    }

    /**
     * Get the request method with spoofing support
     * 
     * @return String The request method
     * 
     * How methor spoofing works:
     * 1. Check if this is a POST request
     * 2. If yes, check if there's a _method field in the POST data
     * 3. If _method exists, use that value (PUT, PATCH, DELETE)
     * 4. Otherwise, use the actual HTTP method
     * 
     * This allos HTML forms (which only support GET/POST)
     * to simulate PUT, PATCH, and DELETE requests.
     * 
     * Example form:
     * <form method="POST">
     *  <input type="hidden" name="_method" value="DELETE">
     *  <!-- Browser sends POST, but we treat it as DELETE -->
     * </form>
     */
    protected function getRequestMethod(): string
    {
        $method = $this->request->method();

        // Only check for method spoofing on POST requests
        if ($method === 'POST') {
            $spoofedMethod = $this->request->input('_method');

            if ($spoofedMethod) {
                // Validate that the spoofed method is allowed

                $allowedMethods = ['PUT', 'PATCH', 'DELETE'];
                $spoofedMethod = strtoupper($spoofedMethod);

                if (in_array($spoofedMethod, $allowedMethods)) {
                    return $spoofedMethod;
                }
            }
        }

        return $method;
    }

    /**
     * Run the request through the middleware pipeline
     * 
     * @param array $middleware Array of middleware class names
     * @param Closure $destination The final controller action
     * @return Response
     */
    protected function runThroughMiddleware(array $middleware, Closure $destination): Response
    {
        $pipeline = new Pipeline($this->container);

        return $pipeline
            ->send($this->request)
            ->through($middleware)
            ->then($destination);
    }

    /**
     * Resolve method arguments by injecting Request where type-hinted
     * and filling remaining parameters with route matches.
     */
    private function resolveMethodArgs($controller, string $method, Request $request, array $routeParams): array
    {
        $reflection = new \ReflectionMethod($controller, $method);
        $args = [];
        $routeParams = array_values($routeParams);

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin() && is_a($request, $type->getName())) {
                $args[] = $request;
            } else {
                $args[] = array_shift($routeParams);
            }
        }

        return $args;
    }

    private function convertRouteToRegex($uri)
    {
        // Convert route placeholders like {id} to a regex capture group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $uri);
        // Escape forward slashes and add start/end anchors
        return '#^' . str_replace('/', '\/', $pattern) . '\/?$#';
    }
}
