<?php

namespace App\Models;

use Core\Database;

class Post
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all posts from the database
     */
    public function getAllPosts()
    {
        $this->db->query("SELECT * FROM posts ORDER BY created_at DESC");
        return $this->db->fetchAll();
    }

    /**
     * Get a single post by its ID.
     */
    public function getPostById($id)
    {
        $this->db->query("SELECT * FROM posts WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Create a new post
     */
    public function createPost($data)
    {
        $this->db->query("INSERT INTO posts (title, body) VALUES (:title, :body)");

        // Bind values
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':body', $data['body']);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update a post.
     */
    public function updatePost($data)
    {
        $this->db->query("UPDATE posts SET title = :title, body = :body WHERE id = :id");

        // Bind values
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':body', $data['body']);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete a post.
     */
    public function deletePost($id)
    {
        $this->db->query("DELETE FROM posts WHERE id = :id");
        $this->db->bind(':id', $id);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
