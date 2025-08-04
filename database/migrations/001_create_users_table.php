<?php

// This is a simplified migration file. Later on (TODO:) we'll build a real runner later. (At the moment it'll only work by running this file on the terminal)

require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Database;

try {
    $db = Database::getInstance()->getPdo();

    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    $db->exec($sql);
    echo "Migration successful: 'users' table created";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
