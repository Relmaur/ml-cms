<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Interfaces\MiddlewareInterface;
use Core\Http\Request;
use Core\Http\Response;
use Core\Security\Csrf;
use Closure;

/**
 * CSRF Middleware
 * 
 * Validates CSRF tokens on state-changing requests (POST, PUT, PATCH, DELETE).
 * If the token is missing or invalid, returns a 419 error.
 * 
 * Safe methods (GET, HEAD, OPTIONS) are allowed through without validation.
 * 
 * Usage:
 * Route::post('/posts', [PostsController::class, 'store'])
 *  ->middleware([AuthMiddleware::class, CsrfMiddleware::class]);
 * 
 * Or apply globally to all routes in a group:
 * Route::group(['middleware' => [CsrfMiddleware::class]], function() {
 * // All POST/PUT/DELETE routes here are protected
 * }):
 */
class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * HTTP methods that don't require CSRF protection
     * These are "safe" methods that shouldn't change state
     */
    protected $except = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip CSRF validation for safe methods
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        // Get the token from the request
        $token = $this->getTokenFromRequest($request);

        // Validate the token
        if (!$token || !Csrf::validateToken($token)) {
            return $this->tokenMismatchResponse();
        }

        // Token is valid, continue to next middleware/controller
        return $next($request);
    }

    /**
     * Check if CSRF validation should be skipped for this request
     * 
     * @param Request $request
     * @return bool
     */
    protected function shouldSkip(Request $request): bool
    {
        return in_array($request->method(), $this->except);
    }

    /**
     * Get the CSRF token from the request
     * 
     * Checks in this order:
     * 1. POST data (_token field)
     * 2. Request headers (X-CSRF-TOKEN)
     * 3. Request headers (X-XSRF-TOKEN)
     * 
     * @param Request $request
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        // Check POST data first (from form field)
        $token = $request->input(Csrf::FIELD_NAME);

        if ($token) {
            return $token;
        }

        // Check headers (for AJAX requests)
        $token = $request->header('X-XSRF-TOKEN');

        if ($token) {
            return $token;
        }

        // Check alternative header name (used by same frameworks)
        return $request->header('X-XSRF-TOKEN');
    }

    /**
     * Return a 419 response for token mismatch
     * 
     * @return Response
     */
    protected function tokenMismatchResponse(): Response
    {

        // In production, one might to render a nice error page
        http_response_code(419);

        ob_start();
?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>419 - Page Expired</title>
            <style>
                body {
                    font-family: sans-serif;
                    max-width: 600px;
                    margin: 100px auto;
                    padding: 20px;
                    text-align: center;
                }

                h1 {
                    color: #721c24;
                }

                p {
                    color: #666;
                }

                a {
                    color: #007bff;
                    text-decoration: none;
                }

                a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>

        <body>
            <h1>419 - Page Expired</h1>
            <p>Your session has expired or the CSRF token is invalid.</p>
            <p>This usually happens when:</p>
            <ul style="text-align: left;">
                <li>You left the page open for too long</li>
                <li>You have multiple tabs open and logged out in one of them</li>
                <li>Your session expired</li>
            </ul>
            <p><a href="javascript:history.back()">← Go Back</a> and try again, or <a href="/">Return to Homepage</a></p>
        </body>

        </html>
<?php
        $content = ob_get_clean();

        return new Response($content, 419);
    }
}
