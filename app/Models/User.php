<?php

namespace App\Models;

use Core\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find a user by their email address
     */
    public function findByEmail($email)
    {
        $this->db->query("SELECT * FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->fetch();
    }

    /**
     * Register a new user
     */
    public function register($data)
    {
        $this->db->query("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");

        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
