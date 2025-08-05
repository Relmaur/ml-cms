
<?php

use Core\Interfaces\MigrationInterface;
use Core\Database;

class CreatePostsTable implements MigrationInterface
{
    public function up(): void
    {
        $db = Database::getInstance()->getPdo();
        // Your UP migration code goes here:
        // $db->exec("CREATE TABLE example (id INTEGER PRIMARY KEY");");
        $db->exec("
            CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    public function down(): void
    {
        $db = Database::getInstance()->getPdo();
        // Your DOWN migration code goes here
        // $db->exec("DROP TABLE IF EXISTS example);");
        $db->exec("DROP TABLE IF EXISTS posts;");
    }
}