<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Database;

$db = Database::getInstance()->getPdo();
$allFiles = glob('database/migrations/*.php');
$stmt = $db->query("SELECT migration FROM migrations");
$runMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
$toRun = array_diff(array_map('basename', $allFiles), $runMigrations);

if (empty($toRun)) {
    echo "Database is already up to date.\n";
    exit;
}

foreach ($toRun as $migrationFile) {
    try {
        echo "Running migration: {$migrationFile}...\n";
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

            echo "Success: {$migrationFile}\n";
        } else {
            throw new Exception("Class {$className} not found in {$migrationFile}");
        }
    } catch (Exception $e) {
        echo "Error running migration {$migrationFile}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "All new migrations have been run successfully";
