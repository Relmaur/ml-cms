<?php

require_once '../vendor/autoload.php';

use Core\Router;
use Core\Session;

// Start the session on every requrest
Session::start();

$router = new Router();
