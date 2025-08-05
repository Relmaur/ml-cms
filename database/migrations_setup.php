<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Core\Database;

try {
    $db = Database::getInstance()->getPdo();
    $sql = "
        CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            batch INTEGER DEFAULT 1
        );
    ";

    $db->exec($sql);
    echo "Migration system boostrapped: 'migrations' table created successfully.\n";
} catch (Exception $e) {
    // Handle exception
    die("Bootstrap failed: " . $e->getMessage());
}
