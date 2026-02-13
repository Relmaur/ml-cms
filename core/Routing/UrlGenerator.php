<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Route;

/**
 * URL Generator
 * 
 * Generates URLs from route names and parameters.
 * This is the engine behind th1 route() helper function.
 * 
 * How it works:
 * 1. Look up the route by name
 * 2. Get the URI pattern (e.g., /posts/{id}/edit)
 * 3. Replace placeholders with provided parameters (e.g., ['id' => 5] -> /posts/5/edit)
 * 4. Return the complete URL
 * 
 * Example:
 * $generator = new UrlGenerator();
 * $url = $generator->route('posts.show', ['id' => 5]);
 * // Return: /posts/5
 */
class UrlGenerator
{

    /**
     * Generate a URL for a named route
     * 
     * @param string $name The route name (e.g., 'posts.show')
     * @param array $parameters Route parameters (e.g., ['id' => 5])
     * @param bool $absolute Whether to generate absolute URL (with domain)
     * @return string The generated URL
     * @throws \Exception if the route name doesn't exist
     */
    public function route(string $name, array $parameters = [], bool $absolute = false): string
    {

        // Look up the route by name
        $route = Route::getByName($name);

        if (!$route) {
            throw new \Exception("Route '{$name}' not found. Did you forget to add ->name('{$name}') to your route?");
        }

        // Start with the URI patthern (e.g., /posts/{id}/edit)
        $route = $route['uri'];

        // Replace route parameters
        // Example: /posts/{id}/edit with ['id' => 5] becomes /posts/5/edit
        $uri = $this->replaceParameters($route, $parameters);

        return $uri;
    }

    /**
     * Replace placeholders in URI with actual parameter values
     * 
     * Process:
     * 1. Find all {parameter} placeholders in the URI
     * 2. For each placeholder, replace with the provided value
     * 3. Throw exception if a required parameter is missing
     * 
     * Examples:
     * - URI /posts/{id}, Parameters: ['id' => 5]
     * Result: /posts/5
     * 
     * - URI /users/{userId}/posts/{postId}, Parameters: ['userId' => 3, 'postId' => 10]
     * Result: /users/3/posts/10
     * 
     * @πaram string $uri The URI pattern with placeholders
     * @param string $uri The URI pattern with placeholders
     * @param array $parameters The parameter values
     * @return string The URI with placeholders replaced
     * @throws \Exception if a rquired parameter is missing
     */
    protected function replaceParameters(string $uri, array $parameters): string
    {

        // Find all {parameter} placeholders
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $uri, $matches);

        // $matches[0] = ['{id}', '{postId}'] (full matches)
        // $matches[1] = ['id', 'postId'] (parameter names)

        foreach ($matches[1] as $parameterName) {
            // Check if parameter was provided
            if (!isset($parameters[$parameterName])) {
                throw new \Exception("Missing required parameter '{$parameterName}' for route.");
            }

            // Replace {parameterName} with the actual value
            $uri = str_replace(
                '{' . $parameterName . '}',
                (string) $parameters[$parameterName],
                $uri
            );
        }

        return $uri;
    }

    /**
     * Get the base URL (scheme + host)
     * 
     * Example: http://localhost:8000 or https://example.com
     * 
     * @return string
     */
    protected function getBaseUrl(): string
    {

        // Determine if we're using HTTPS
        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === 443)
        );

        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host;
    }

    /**
     * Generate an absolute URL for a named route
     * 
     * This is a convenience method that calls route() with $absolute = true
     * 
     * @param string $name The route name
     * @param array $parameters Route parameters
     * @return string The absolute URL
     */
    public function absoluteRoute(string $name, array $parameters = []): string
    {
        return $this->route($name, $parameters, true);
    }
}
