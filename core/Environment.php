<?php

namespace Core;

use Symfony\Component\Dotenv\Dotenv;

/**
 * Environment Configuration Manager
 * 
 * Loads and manages environment variables from .env files.
 * This allows different configurations for development, staging, dev, etc.
 * without changing code or committing sensitive data
 * 
 Usage:
 Environment::load();
 $debug = Environment::get('APP_DEBUG', false);
 $dbConnection = Environment::get('DB_CONNECTION', 'sqlite');
 */
class Environment
{
    /**
     * Whether the environment has been loaded
     */
    protected static $loaded = false;

    /**
     * Load environment variables from .env file
     * 
     * This method should be called ONCE at the start of your application,
     * typically in bootstrap.php or index.php
     * 
     * How it works:
     * 1. Looks for .env file in your root directory
     * 2. Parses the file and loads variables into $_ENV and $_SERVER
     * 3. Variables can then be accessed via env() helper or getenv()
     * 
     * File proprity (if multiple exists):
     * 1. .env.local (local overrides, never commit)
     * 2. .env.{APP_ENV} (e.g., .env.production)
     * 3. .env (default configuration, can commit)
     * 
     * @param string $path Path to the directory containing .env file
     * @return void
     */
    public static function load($path = null)
    {

        // Only load once
        if (self::$loaded) {
            return;
        }

        $envFile = $path . '/.env';

        // Check if .env file exists
        if (!file_exists($envFile)) {
            // In production, .env might not exists if using server environment variables
            // This is okay - just mark as loaded and continue
            self::$loaded = true;
            return;
        }

        try {
            // Creates Dotenv instance and load the file
            $dotenv = new Dotenv();
            $dotenv->load($envFile);

            self::$loaded = true;
        } catch (\Exception $e) {
            // If loading fails, log the error but don't crash the app
            error_log('Failed to load .env file: ' . $e->getMessage());
        }
    }

    /**
     * Get an environment variable value
     * 
     * Checks in this order:
     * 1. $_ENV array
     * 2. $_SERVER array
     * 3. getenv() function
     * 4. Returns default if not found
     * 
     * Type casting:
     * - 'true' and 'false' strings -> boolean
     * - 'null' string -> null
     * - Empty string -> null (unless default is string)
     * 
     * Usage:
     * Environment::get('APP_DEBUG', false)
     * Environment::get('DB_CONNECTION', 'sqlite')
     * Environment::get('DB_PORT', 3306)
     * 
     * @param string $key The environment variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get($key, $default = null)
    {

        // Try $_ENV first (set by Dotenv)
        if (isset($_ENV[$key])) {
            return self::parseValue($_ENV[$key]);
        }

        // Try $_SERVER (set by web server or Dotenv)
        if (isset($_SERVER[$key])) {
            return self::parseValue($_SERVER[$key]);
        }

        // Try getenv() (fallback for some server configurations)
        $value = getenv($key);
        if ($value !== false) {
            return self::parseValue($value);
        }

        // Return default if not found
        return $default;
    }

    /**
     * Parse environment variable value
     * 
     * Converts string representations to proper types:
     * - 'true' -> true
     * - 'false' -> false
     * - 'null' -> null
     * - '(empty)' or '()' -> ''
     * - '"value"' -> 'value' (strips quotes)
     * 
     * This matches how Dotenv parses values in other frameworks.
     * 
     * @param mixed $value The raw value from environment
     * @return mixed Parsed value
     */
    protected static function parseValue($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        // Handle special string values
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
        }

        // Remove quotes if present
        if (strlen($value) > 1 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Check if we're in a specific environment
     * 
     * Usage:
     * if(Environment::is('local')) {
     *  // Show debug toolbar
     * }
     * 
     * if(Environment::is('production')) {
     *  // Enable caching
     * }
     * 
     * @param string $environment The environment name to check
     * @return bool
     */
    public static function is($environment)
    {
        return self::get('APP_ENV', 'production') === $environment;
    }

    /**
     * Check if we're in production
     * 
     * @return bool
     */
    public static function isProduction()
    {
        return self::is('production');
    }

    /**
     * Check if we're in local/development
     * 
     * @return bool
     */
    public static function isLocal()
    {
        return self::is('local');
    }

    /**
     * Get all environment variables
     * 
     * Useful for debugging (but be careful not to expose in production!)
     * 
     * @return array
     */
    public static function all()
    {
        return $_ENV;
    }
}


