<?php

require_once '../vendor/autoload.php';

use Core\Router;
use Core\Session;

// Get the container with all the bindings
$container = require_once '../bootstrap.php';

// Start the session on every request
Session::start();

require_once '../routes/web.php';
require_once '../routes/api.php';

// Dispatch the router
$router = new Router($container);
$response = $router->dispatch();
$response->send();
