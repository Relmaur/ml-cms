<?php

namespace Core;

class Router
{
    protected $controller = 'App\Controllers\PagesController';
    protected $method = 'home';
    protected $params = [];

    public function __construct()
    {
        $this->parseUrl();
        $this->dispatch();
    }

    /**
     * Parses the URL into controller, method and parameters.
     */
    protected function parseUrl()
    {
        $url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        if (!empty($url)) {
            $url = explode('/', $url);

            // Set controller
            if (isset($url[0])) {

                $controllerName = 'App\\Controllers\\' . ucfirst($url[0]) . 'Controller';

                if (class_exists($controllerName)) {
                    $this->controller = $controllerName;
                    unset($url[0]);
                }
            }

            // Set method
            if (isset($url[1])) {
                if (method_exists($this->controller, $url[1])) {
                    $this->method = $url[1];
                    unset($url[1]);
                }
            }

            // Set params
            $this->params = $url ? array_values($url) : [];
        }

        if(!method_exists($this->controller, $this->method)) {
            if(method_exists($this->controller, 'index')) {
                $this->method = 'index';
            } else {
                throw new \Exception("Method {$this->method} not found in controller {$this->controller}");
            }
        }
    }

    /**
     * Dispatches the request to the appropriate controller and method.
     */
    protected function dispatch()
    {
        $controllerInstance = new $this->controller;
        call_user_func_array([$controllerInstance, $this->method], $this->params);
    }
}
