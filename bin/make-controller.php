<?php
require_once 'vendor/autoload.php';

use Core\Console;

if ($argc < 2) {
    Console::error("Usage: php bin/make-controller.php <ControllerName>");
    Console::info("Example: php bin/make-controller.php ProductsController");
    exit(1);
}

$className = $argv[1];

// Ensure the name ends with Controller
if (substr($className, -10) !== 'Controller') {
    $className .= 'Controller';
};

$stubPath = 'stubs/controller.stub';
$controllerPath = "app/Controllers/{$className}.php";

if (file_exists($controllerPath)) {
    Console::error("Error: Controller '{$className}' already exists.");
    exit(1);
}

// Read the stub file
$stub = file_get_contents($stubPath);
if ($stub == false) {
    Console::error("Error: Unable to read stub file at {$stubPath}");
    exit(1);
}

// Determine the view path from the controller name (e.g., ProductsController -> products)
$viewPath = strtolower(str_replace('Controller', '', $className));

// Replace placeholders
$stub = str_replace('{{ClassName}}', $className, $stub);
$stub = str_replace('{{ViewPath}}', $viewPath, $stub);

// Write the new controller file
if (file_put_contents($controllerPath, $stub) == false) {
    Console::error("Error: Unable to create controller file at {$controllerPath}");
    exit(1);
}

Console::success("Controller created successfully: {$controllerPath}");
Console::warning("Remember to add the binding to bootstrap.php!");
