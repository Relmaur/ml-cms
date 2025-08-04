<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Database;

try {
    $db = Database::getInstance()->getPdo();

    // SQL statement to create the posts table
    $sql = "
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    $db->exec($sql);
    echo "Table 'posts' created successfully. \n";

    // SQL statement to insert dummy data
    $insertSql = "
        INSERT INTO posts (title, body) VALUES
        ('My First Post', 'This is the body of my vwey first post. Welcome!'),
        ('Learning MVC', 'The Model-View_Controller pattern is great for organizing code.'),
        ('PHP is Awesome', 'Building things from scratch is a fantastic way to learn.')
    ";

    // Check if posts table is empty before inserting
    $result = $db->query("SELECT COUNT(*) FROM posts");
    $count = $result->fetchCOlumn();

    if ($count == 0) {
        $db->exec($insertSql);
        echo "Dummy data inserted successfully.\n";
    } else {
        echo "Posts table already contains data. Skipping insertion.\n";
    }
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
