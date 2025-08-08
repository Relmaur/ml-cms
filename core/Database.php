<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;
    private $stmt;

    private function __construct()
    {

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
     * A method to get the raw PDO object for setup scripts or complex queries.
     */

    public function getPdo()
    {
        return $this->pdo;
    }

    /*
       ========================
          MARK: Method Chaining
       ========================
    */

    /**
     * Prepares an SQL query.
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

        // Returnthe database after the value bindings
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
}
