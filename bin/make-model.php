<?php
require_once "vendor/autoload.php";

use Core\Console;

if ($argc < 2) {
    Console::error("Usage: php bin/make-model.php <ModelName>");
    Console::info("Example: php bin/make-model.php Category");
    exit(1);
}

$className = ucfirst($argv[1]);
$stubPath = 'stubs/model.stub';
$modelPath = "app/Models/{$className}.php";

if (file_exists($modelPath)) {
    Console::error("Error: Model '{$modelPath} already exists.");
    exit(1);
}

$stub = file_get_contents($stubPath);
if ($stub === false) {
    Console::error("Error: Unable to read stub file at: {$stubPath}");
    exit(1);
}

$stub = str_replace('{{ClassName}}', $className, $stub);

if (file_put_contents($modelPath, $stub) === false) {
    Console::error("Error: Unable to create model file at {$modelPath}");
    exit(1);
}

Console::success("Model {$modelPath} created successfully");
Console::warning("Remember to add the binding to bootstrap.php");
