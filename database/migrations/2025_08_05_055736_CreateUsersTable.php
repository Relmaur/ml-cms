
<?php

use Core\Interfaces\MigrationInterface;
use Core\Database;

class CreateUsersTable implements MigrationInterface
{
    public function up(): void
    {
        $db = Database::getInstance()->getPdo();
        // Your UP migration code goes here:
        // $db->exec("CREATE TABLE example (id INTEGER PRIMARY KEY");");
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    public function down(): void
    {
        $db = Database::getInstance()->getPdo();
        // Your DOWN migration code goes here
        // $db->exec("DROP TABLE IF EXISTS example);");
        $db->exec("DROP TABLE IF EXISTS users;");
    }
}
