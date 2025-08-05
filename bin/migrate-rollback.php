<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Core\Database;

$db = Database::getInstance()->getPdo();

// Find the last migration batch number
$stmt = $db->query("SELECT MAX(batch) FROM migrations");
$lastBatch = $stmt->fetchColumn();

if (!$lastBatch) {
    echo "No migrations to roll back.\n";
    exit;
}

// Get all migrations from the last batch
$stmt = $db->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY migration DESC");
$stmt->execute([$lastBatch]);
$migrationsToRollback = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Roll them back
foreach ($migrationsToRollback as $migrationFile) {
    try {
        echo "Rolling back: {$migrationFile}";

        require_once 'database/migrations/' . $migrationFile;
        $className = substr(pathinfo($migrationFile, PATHINFO_FILENAME), 18);
        if (class_exists($className)) {
            $migrationInstance = new $className();
            $migrationInstance->down();

            // Remove the migration record from the database
            $deleteStmt = $db->prepare("DELETE FROM migrations WHERE migration = ?");
            $deleteStmt->execute([$migrationFile]);

            echo "Success: Rolled back {$migrationFile}\n";
        } else {
            throw new Exception("Class {$className} not found in {$migrationFile}");
        }
    } catch (Exception $e) {
        echo "Error rolling back migration {$migrationFile}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Rollback completed successfully";
