<?php

if ($argc < 2) {
    die("Usage: php bin/make-controller.php <ControllerName>\nExample: php bin/make-controller.php ProductsController\n");
}

$className = $argv[1];

// Ensure the name ends with Controller
if (substr($className, -10) !== 'Controller') {
    $className .= 'Controller';
};

$stubPath = 'stubs/controller.stub';
$controllerPath = "app/Controllers/{$className}.php";

if (file_exists($controllerPath)) {
    die("Error: Controller '{$className}' already exists.\n");
}

// Read the stub file
$stub = file_get_contents($stubPath);
if ($stub == false) {
    die("Error: Unable to read stub file at {$stubPath}");
}

// Determine the view path from the controller name (e.g., ProductsController -> products)
$viewPath = strtolower(str_replace('Controller', '', $className));

// Replace placeholders
$stub = str_replace('{{ClassName}}', $className, $stub);
$stub = str_replace('{{ViewPath}}', $viewPath, $stub);

// Write the new controller file
if (file_put_contents($controllerPath, $stub) == false) {
    die("Error: Unable to create controller file at {$controllerPath}\n");
}

echo "Controller created successfully: {$controllerPath}\n";
echo "Remember to add the binding to bootstrap.php!";
