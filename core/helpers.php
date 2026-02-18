<?php

declare(strict_types=1);

/**
 * Global Helper Functions
 * 
 * These functions are available throughout the application.
 * Loaded automatically by the autoloader.
 */

use Core\Security\Csrf;

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token hidden input field
     * 
     * @return string
     */

    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the current CSRF token value
     * 
     * @return string
     */
    function csrf_token(): string
    {
        return Csrf::getToken();
    }
}

if (!function_exists('csrf_meta')) {
    /**
     * Generate a CSRF token meta tag
     * 
     * @return string
     */
    function csrf_meta(): string
    {
        return Csrf::metaTag();
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value from session (for repopulating forms after validation errors)
     * 
     * @param string $key The input field name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function old(string $key, mixed $default = ''): mixed
    {
        $oldInput = Core\Session::getFlash('old_input') ?? [];
        return $oldInput[$key] ?? $default;
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities (shorter alias for e)
     * 
     * @param string $value
     * @return string
     */
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate a hidden input field for HTTP method spoofing
     * 
     * HTML forms only support GET and POST, but we want to use PUT, PATCH, DELETE.
     * This helper creates a hidden field that tells our router to treat the request
     * as a different HTTP method.
     * 
     * Usage in a form:
     * <form method="POST" action="/items/5">
     *  <?php echo method_field('DELETE'); ?>
     *  <button type="submit">Delete Item</button>
     * </form>
     * 
     * @param string $method The HTTP method (PUT, PATCH, or DELETE)
     * @return string HTML for hidden input field
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('route')) {
    /**
     * Generate a URL for a named route
     * 
     * This is probably the most commonly used helper in the entire framework.
     * It allows you to generate URLs without hardcoding paths.
     * 
     * Basic usage:
     * route('posts.index')
     * route('posts.show', ['id' => 5])
     * route('posts.edit', ['id' => 5])
     * 
     * With multiple parameters:
     * route('users.posts.show', ['userId' =>1, 'postId' => 5]) -> /users/1/posts/5
     * 
     * Absolute URLs (with domain):
     * route('posts.show', ['id' => 5], true) -> http://example.com/posts/5
     * 
     * @param string $name The route name
     * @param array $parameters Route parameters
     * @param bool $absolute Whether to generate an absolute URL
     * @return string The generated URL
     */
    function route(string $name, array $parameters = [], bool $absolute = false): string
    {
        static $generator = null;

        // Create the URL generator once and reuse it
        if ($generator === null) {
            $generator = new \Core\Routing\UrlGenerator();
        }

        return $generator->route($name, $parameters, $absolute);
    }

    if (!function_exists('absolute_route')) {

        /**
         * Generate an absolute URL for a named route
         * 
         * Convenience wrapper around route() that always generates absolute URLs.
         * 
         * @πaram string $name The route name
         * @param array $parameters Route parameters
         * @return string The absolute URL (with domain)
         */
        function absolute_route(string $name, array $parameters = []): string
        {
            return route($name, $parameters, true);
        };
    }
}

if (!function_exists('env')) {
    /**
     * Get an environment variable value
     * 
     * This is a shorthand for Environment::get()
     * 
     * Usage:
     * env('APP_DEBUG', false)
     * env('DB_CONNECTION', 'sqlite')
     * env('APP_URL', 'http://localhost')
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        return \Core\Environment::get($key, $default);
    }
}
