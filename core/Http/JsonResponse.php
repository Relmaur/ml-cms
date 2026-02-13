<?php

declare(strict_types=1);

namespace Core\Http;

/**
 * JSON Response
 * 
 * A specialized response class for sending JSON data.
 * Automatically sets the Content-Type header to application/json
 * 
 * Usage:
 * return new JsonResponse(['success' => true, 'data' => $items]);
 * 
 * Or from a closure route:
 * Route::get('/api/items', function($request) {
 *  return ['items' => Item::all()]; // Automatically converted to JsonResponse
 * });
 */
class JsonResponse extends Response
{

    /**
     * Create a new JSON response
     * 
     * @param mixed $data The data to encode as JSON
     * @param int $status HTTP status code (default: 200)
     * @param array $headers Additional headers
     */
    public function __construct($data = null, $status = 200, array $headers = [])
    {

        // Encode data as JSON
        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Set Content-Type header to application/json
        $headers['Content-Type'] = 'application/json';

        // Call parent constructor
        parent::__construct($content, $status, $headers);
    }

    /**
     * Set the data to be encoded to JSON
     * 
     * @param mixed $data
     * @return $this
     */
    public function setData($data): self
    {
        $this->content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        return $this;
    }
}
