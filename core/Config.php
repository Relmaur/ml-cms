<?php

namespace Core;

/**
 * Configuration Manager
 * 
 * Provides a centralized way to access configuration values.
 * Can load from .env or from config files.
 * 
 * User:
 * Config::get('app.name', 'Default APp Name')
 * Config::get('database.connection', 'sqlite')
 */
class Config
{

    /**
     * Configuration values cache
     */
    protected static $items = [];

    /**
     * Get a configuration value
     * 
     * SUpports dot notation for nested values:
     * Config::get('database.connection')
     * 
     * First checks loaded config, then falls back to env()
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {

        // Check if value is cached
        if (isset(self::$items[$key])) {
            return self::$items[$key];
        }

        // Parse dot notation (e.g., 'app.name' -> 'APP_NAME')
        $envKey = strtoUpper(str_replace('.', '_', $key));

        // Get from environment
        $value = env($envKey, $default);

        // Cache it
        self::$items[$key] = $value;

        return $value;
    }

    /**
     * Set a configuration value (runtime only)
     * 
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        self::$items[$key] = $value;
    }

    /**
     * Check if a configuration key exists
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset(self::$items[$key]) || env(strtoupper(str_replace('.', '_', $key))) !== null;
    }

    /**
     * Get all configuration items
     * 
     * @return array
     */
    public static function all()
    {
        return self::$items;
    }
}
