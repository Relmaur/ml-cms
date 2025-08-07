<?php

namespace Core;

use Exception;

class Container
{
    protected $bindings = [];

    /**
     * Bind a "recipe" for creating a class into the container.
     */
    public function bind($key, $resolver)
    {
        $this->bindings[$key] = $resolver;
    }

    /**
     * Resolve a class from the container (build it)
     */
    public function resolve($key)
    {
        if (!array_key_exists($key, $this->bindings)) {
            throw new Exception("No matching binding found for {$key}");
        }

        $resolver = $this->bindings[$key];

        // The resolver is a function that creates the object
        return call_user_func($resolver);
    }
}
