
<?php

use Core\Interfaces\MigrationInterface;
use Core\Database;

class AddExcerptToPostsTable implements MigrationInterface
{
    public function up(): void
    {
        $db = Database::getInstance()->getPdo();
        // Your UP migration code goes here:
        // $db->exec("CREATE TABLE example (id INTEGER PRIMARY KEY");");
        $db->exec("
            ALTER TABLE posts
            ADD COLUMN excerpt TEXT;
        ");
    }

    public function down(): void
    {
        $db = Database::getInstance()->getPdo();
        // Your DOWN migration code goes here
        // $db->exec("DROP TABLE IF EXISTS example);");
        $db->exec("ALTER TABLE posts DROP COLUMN excerpt;");
    }
}
