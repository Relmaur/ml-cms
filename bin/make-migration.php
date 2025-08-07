<?php

require_once 'vendor/autoload.php';

use Core\Console;

if ($argc < 2) {
    Console::error("Usage: php make-migration <migration_name>");
    exit(1);
}

$className = $argv[1];
$timestamp = date('Y_m_d_His');
$filename = "database/migrations/{$timestamp}_{$className}.php";

$stub = <<<EOD

<?php

use Core\Interfaces\MigrationInterface;
use Core\Database;

class {$className} implements MigrationInterface {
    public function up(): void {
        \$db = Database::getInstance()->getPdo();
        // Your UP migration code goes here:
        // \$db->exec("CREATE TABLE example (id INTEGER PRIMARY KEY");");
    }

    public function down(): void {
        \$db = Database::getInstance()->getPdo();
        // Your DOWN migration code goes here
        // \$db->exec("DROP TABLE IF EXISTS example);");
    }
}

EOD;

if (file_put_contents($filename, $stub) == false) {
    Console::error("Error creating migration file.");
    exit(1);
}

Console::success("Created migration: {$filename}");
