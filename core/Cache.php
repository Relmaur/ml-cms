<?php

namespace Core;

class Cache
{
    private static $cacheDir = __DIR__ . '/../storage/cache/';

    /**
     * Get an item from the cache
     * 
     * @param string $key The unique key for the cache item.
     * @param mixed The cached data or false if not found or expired.
     */

    public static function get($key)
    {
        $file = self::$cacheDir . md5($key) . '.cache';

        if (!file_exists($file)) {
            return false;
        }

        $data = unserialize(file_get_contents($file));

        // Check if the cache has expired
        if (time() > $data['expires']) {
            self::forget($key);
            return false;
        }

        return $data['data'];
    }

    /**
     * Store an item in the cache
     * 
     * @param string $key The unique key for the cache item.
     * @param mixed $value The data to cache.
     * @param int $minutes The number of minutes to cache the data.
     */

    public static function put($key, $value, $minutes = 10)
    {
        $file = self::$cacheDir . md5($key) . '.cache';

        $data = [
            'expires' => time() + ($minutes * 60),
            'data' => $value,
        ];

        // Ensure the cache directory exists, if not, create it.
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }

        file_put_contents($file, serialize($data));
    }

    /**
     * Remove an item from the cache
     * 
     * @param string $key The unique key for the cache item.
     */
    public static function forget($key)
    {
        $file = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
