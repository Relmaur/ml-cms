<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Core\Database;
use Core\Container;
use Core\Session;

class BaseFeatureTestCase extends TestCase
{

    protected $pdo;
    protected $container;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {

        parent::setUp();

        echo "DEBUG: setUp() started.\n";

        // Bootstrap application to get the controller
        // $this->container = require __DIR__ . '/../../bootstrap.php';

        $bootstrapPath = __DIR__ . '/../../bootstrap.php';
        if (!file_exists($bootstrapPath)) {
            die("FATAL ERROR: bootstrap.php not found at {$bootstrapPath}\n");
        }
        $this->container = require $bootstrapPath;
        echo "DEBUG: Container bootstrapped.\n";


        // Start the session for testing
        Session::start();
        echo "DEBUG: Session started.\n";

        $this->pdo = Database::getInstance()->getPdo();
        echo "DEBUG: Database instance created.\n";

        // Run all migrations to set up the schema
        $this->runMigrations();
        echo "DEBUG: setUp() finished.\n";
    }

    /**
     * This method gets called after each test
     */
    protected function tearDown(): void
    {
        // Destroy the session
        Session::destroy();

        // This is not strictly necessary with an in-memory DB,
        // but good practice for file-based test DBs.
        $this->pdo = null;
        $this->container = null;
        parent::tearDown();
    }

    private function runMigrations()
    {
        echo "DEBUG: Running migrations...\n";
        $migrationPath = __DIR__ . '/../../database/migrations/*.php';
        $migrationFiles = glob($migrationPath);

        if (empty($migrationFiles)) {
            echo "DEBUG WARNING: No migration files found at {$migrationPath}\n";
        }

        // $migrationFiles = glob(__DIR__ . '/../../database/migrations/*.php');

        foreach ($migrationFiles as $file) {

            echo "DEBUG: Found migration file: {$file}\n";

            require_once $file;

            $className = pathinfo($file, PATHINFO_FILENAME);
            $className = substr($className, 18);

            if (class_exists($className)) {
                $migration = new $className();
                $migration->up();
                echo "DEBUG: Ran migration for class {$className}\n";
            } else {
                echo "DEBUG ERROR: Class {$className} not found in file {$file}\n";
            }
        }

        echo "DEBUG: Migrations finished.\n";
    }
}
