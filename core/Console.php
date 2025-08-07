<?php

namespace Core;

class Console
{
    // ANSI color codes
    private const COLOR_DEFAULT = "\033[0m";
    private const COLOR_GREEN = "\033[0;32m";
    private const COLOR_RED = "\033[0;31m";
    private const COLOR_YELLOW = "\033[1;33m";
    private const COLOR_BLUE = "\033[0;34m";

    private static function log($message, $color)
    {
        echo "\n" . $color . $message . self::COLOR_DEFAULT . PHP_EOL;
    }

    public static function success($message)
    {
        echo self::log("✅ SUCCESS: " . $message, self::COLOR_GREEN);
    }

    public static function error($message)
    {
        echo self::log("❌ ERROR: " . $message, self::COLOR_RED);
    }

    public static function info($message)
    {
        echo self::log("ℹ️ INFO: " . $message, self::COLOR_BLUE);
    }

    public static function warning($message)
    {
        echo self::log("⚠️ WARNING: " . $message, self::COLOR_YELLOW);
    }
    
}
