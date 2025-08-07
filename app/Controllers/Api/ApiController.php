<?php

namespace App\Controllers\Api;

abstract class ApiController
{
    /**
     * Send a JSON response.
     * 
     * @param mixed $data The data to send
     * @param int $statusCode The HTTP status code
     */

    protected function jsonResponse($data, $statusCode = 200)
    {
        // Clear any previous output
        ob_clean();

        // Set the HTTP status code and content type header
        http_response_code($statusCode);
        header('Content-Type: application/json');

        // Output the data as JSON and stop execution
        echo json_encode($data);
        exit();
    }
}
