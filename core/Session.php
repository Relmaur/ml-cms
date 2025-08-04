<?php

namespace Core;

class Session
{
    public static function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 1800, // 30 minutes,
                'path' => '/',
                'domain' => '', // Set your domain in production
                'secure' => isset($_SERVER['HTTPS']), // Only send over HTTPS
                'httponly' => true // Prevent Javascript access
            ]);

            session_start();
        }
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        session_unset();
        session_destroy();
    }
}
