<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Database;
use Core\Console;

$db = Database::getInstance()->getPdo();
$allFiles = glob('database/migrations/*.php');
$stmt = $db->query("SELECT migration FROM migrations");
$runMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
$toRun = array_diff(array_map('basename', $allFiles), $runMigrations);

if (empty($toRun)) {
    Console::success("Database is already up to date.");
    exit();
}

foreach ($toRun as $migrationFile) {
    try {
        Console::info("Running migration: {$migrationFile}...");
        require_once 'database/migrations/' . $migrationFile;

        // Extract class name from filename (e.g., 2025_08_04_100000_CreateUsersTable.php -> CreateUsersTable)
        $className = pathinfo($migrationFile, PATHINFO_FILENAME);
        // Remove the timestamp prefix
        $className = substr($className, 18);

        if (class_exists($className)) {
            $migrationInstance = new $className();
            $migrationInstance->up();

            $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            // TODO: to implement batch, at the moment, we'll leave it at 1
            $stmt->execute([$migrationFile, 1]);

            Console::success("Success: {$migrationFile}");
        } else {
            throw new Exception("Class {$className} not found in {$migrationFile}");
        }
    } catch (Exception $e) {
        Console::error("Error running migration {$migrationFile}: " . $e->getMessage());
        exit(1);
    }
}

Console::success("All new migrations have been run successfully");
