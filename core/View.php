<?php

namespace Core;

use Core\Http\HtmlResponse;

class View
{
    /**
     * Render a view file.
     * 
     * @param string $view The view file to render (e.g., 'posts/index')
     * @param array $args The data to pass to the View
     * @param string $layout The specified layout file (under app/Views/layouts)
     */

    public static function render($view, $args = [], $layout = "main")
    {
        // Make variables available to the view
        extract($args, EXTR_SKIP);

        $file = __DIR__ . "/../app/Views/{$view}.php";

        if (is_readable($file)) {
            // Start output buffering
            ob_start();

            // Include the view file
            require $file;

            // Get the content of the buffer
            $content = ob_get_clean();

            // Require the main layout, which will use the $content variable
            require_once __DIR__ . "/../app/Views/layouts/" . $layout . ".php";
            $layoutContent = ob_get_clean();

            return new HtmlResponse($layoutContent);
        }

        // Return a 404 response if view not found
        ob_start();
        require __DIR__ . "/../app/Views/errors/404.php";
        $content = ob_get_clean();
        return new HtmlResponse($content);
    }
}
