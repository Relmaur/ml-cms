<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use Core\QueryBuilder;

/**
 * Database Connection Manager
 * 
 * Handles database connections for SQLite and MySQL based on .env configuration.
 * Uses the singleton pattern to ensure only one connction exists.
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $stmt;

    /**
     * Private constructor (singleton pattern)
     * 
     * Reads DB_CONNECTION from .env and creates appropriate connection:
     * - sqlite: Uses DB_PATH
     * - mysql: Uses DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
     */
    private function __construct()
    {

        $driver = env('DB_CONNECTION', 'sqlite');

        try {
            if ($driver === 'mysql') {
                $this->connectMySQL();
            } else {
                $this->connectSQLite();
            }

            // Set PDO options (same for both drivers)
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) {

            // In production, log the error but show a generic message
            if (env('APP_DEBUG', false)) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                error_log("Database connection failed: " . $e->getMessage());
                die("Database connection failed. Please try again later.");
            }
        }

        /*
        //Check if we are in the testing environment
        if (isset($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] === 'testing') {
            // Use an in-memory SQLite database for tests
            $dsn = "sqlite::memory:";
        } else {

            // Use the regular file-based database for development
            $config = require __DIR__ . '/../config/database.php';

            // DSN for SQLite
            $dsn = "sqlite:" . $config['path'];
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ];

        try {
            $this->pdo = new PDO($dsn, null, null, $options);
        } catch (PDOException $e) {
            // In a prod environment, we'd log this error, not display it
            die('Connection Failed: ' . $e->getMessage());
        }
        */
    }

    /**
     * Connect to SQLite database
     * 
     * Uses DB_PATH from .env (default: database/database.sqlite)
     */
    private function connectSQLite()
    {

        // Get path from .env (relative to project root)
        $dbPath = env('DB_PATH', 'database/database.sqlite');

        // Make it absolute if it's relative
        if (!str_starts_with($dbPath, '/')) {
            $dbPath = dirname(__DIR__) . '/' . $dbPath;
        }

        // Check if database file exists
        if (!file_exists($dbPath)) {
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            // Create empty database file
            touch($dbPath);
        }

        $this->pdo = new PDO('sqlite:' . $dbPath);
    }

    /**
     * Connect to MySQL database
     * 
     * Uses these .env variables:
     * - DB_HOST (default: localhost)
     * - DB_PORT (default: 3306)
     * - DB_DATABASE (required)
     * - DB_USERNAME (required)
     * - DB_PASSWORD (optional)
     */
    private function connectMySQL()
    {
        $host = env('DB_HOST', 'localhost');
        $port = env('DB_PORT', 3306);
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD', '');

        // Validate required settings
        if (!$database || !$username) {
            throw new PDOException('MySQL requires DB_DATABSE and DB_USERNAME in .env file');
        }

        // Build DSN (Data Source Name)
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        $this->pdo = new PDO($dsn, $username, $password);
    }

    /**
     * Gets the single instance of the Database class.
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Start a query builder of a table
     * 
     * This is the main entry point for using the query builder.
     * 
     * Usage:
     * $posts = $db->table('posts')
     *  ->where('status', 'published')
     *  ->orderBy('created_at', 'desc')
     *  ->get();
     * 
     * @param string $table The table name
     * @return QueryBuilder
     */
    public function table($table)
    {
        return new QueryBuilder($this->pdo, $table);
    }

    /*
       ========================
          MARK: Method Chaining
       ========================
    */

    /**
     * Prepares an SQL query (backwared compatibility).
     */
    public function query($sql)
    {
        $this->stmt = $this->pdo->prepare($sql);

        // Return the entire Database
        return $this;
    }

    /**
     * Binds a value to a prepared statement parameter.
     */
    public function bind($param, $value, $type = null)
    {

        // Logic to detect type if not provided
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        // Bind value to the statement
        $this->stmt->bindValue($param, $value, $type);

        // Return the database after the value bindings
        return $this;
    }

    /**
     * Executes the prepared statement.
     */
    public function execute()
    {
        // Finally, return the statement
        return $this->stmt->execute();
    }

    /**
     * Fetches all results from the statement as an array of objects
     */
    public function fetchAll()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetches a single result from the statement as an object.
     */
    public function fetch()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get the current database driver name
     * 
     * Return: 'sqlite' or 'mysql'
     * 
     * @return string
     */
    public function getDriver()
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}
