<?php

declare(strict_types=1);

namespace Core\Security;

use Core\Session;

/**
 * CSRF (Cross-Site Request Forgery) Protection
 * 
 * Generates and validates CSRF tokens to protect agains CSRF attacks.
 * 
 * How it works:
 * 1. Generate a random token and store it in the session.
 * 2. Include the token in every form as a hidden field
 * 3. Validate the token on POST/PUT/DELETE requests
 * 4. Reject requests with missing or invalid tokens.
 * 
 * Usage in views:
 * <form method="POST">
 *  <?php echo Csrf::field() ?>
 *  <!-- rest of form-->
 * </form>
 */

class Csrf
{

    /**
     * Session key for storing the CSRF token
     */
    const TOKEN_KEY = '_csrf_token';

    /**
     * Form field name for the CSRF token
     */
    const FIELD_NAME = '_token';

    /**
     * Generate a new CSRF token and store it in the session
     * 
     * @return string The generated token
     */

    public static function generateToken(): string
    {
        // Generate a cryptographically secure random token
        $token = bin2hex(random_bytes(32)); // 64 character hex string

        // Store in session
        Session::set(self::TOKEN_KEY, $token);

        return $token;
    }

    /**
     * Get the current CSRF token (generates one id it doesn't exist)
     */
    public static function getToken(): string
    {
        $token = Session::get(self::TOKEN_KEY);

        if (!$token) {
            $token = self::generateToken();
        }

        return $token;
    }

    /**
     * Validate a CSRF token
     * 
     * @param string $token The token to validate
     * @return bool
     */
    public static function validateToken($token): bool
    {
        $sessionToken = Session::get(self::TOKEN_KEY);

        // Check if token exists in session
        if (!$sessionToken) {
            return false;
        }

        //Use hash_equals to prevent timing attacks
        // Regular comparison (==) can leak information about the token
        // through timing differences in string comparison.
        return hash_equals($sessionToken, $token);
    }

    /**
     * Generate a hidden input field with the CSRF token
     * 
     * @return string HTML for hidden input field
     */
    public static function field(): string
    {
        $token = self::getToken();
        $fieldName = self::FIELD_NAME;

        return '<input type="hidden" name="' . htmlspecialchars($fieldName) . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Get the CSRF token as a meta tag (useful for AJAX requests)
     * 
     * @return string HTML for meta tag
     */
    public static function metaTag(): string
    {
        $token = self::getToken();

        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Regenerate the CSRF token (call this after login for extra security)
     */
    public static function regenerateToken(): string
    {
        return self::generateToken();
    }
}