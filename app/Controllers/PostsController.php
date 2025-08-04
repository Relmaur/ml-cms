<?php

namespace App\Controllers;

use App\Models\Post;

class PostsController
{
    private $postModel;

    public function __construct()
    {
        $this->postModel = new Post();
    }

    /**
     * The methods declared here, are going to be 'detected' by the Router class, so that each of these methods correspond to a url parameter, for example: /posts/<method>
     */

    /**
     * Show all posts.
     */
    public function index()
    {
        $posts = $this->postModel->getAllPosts();
        $pageTitle = 'All Posts';

        require_once '../app/Views/posts/index.php';
    }

    /**
     * Show a single post.
     */

    public function show($id)
    {
        $post = $this->postModel->getPostById($id);
        $pageTitle = $post ? $post->title : 'Post Not Found';

        require_once '../app/Views/posts/show.php';
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $pageTitle = 'Create New Post';
        require_once '../app/Views/posts/create.php';
    }

    /**
     * Store a new post in the database
     */
    public function store()
    {
        // Basic validation
        if (isset($_POST['title']) && isset($_POST['body']) && !empty($_POST['title']) && !empty($_POST['body'])) {
            $data = [
                'title' => trim($_POST['title']),
                'body' => trim($_POST['body'])
            ];

            // Sanitize data before inserting (!importing)
            $data['title'] = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
            $data['body'] = htmlspecialchars($data['body'], ENT_QUOTES, 'UTF-8');

            if ($this->postModel->createPost($data)) {
                // Redirect to the blog index on success
                header('Location: /posts/index');
                exit();
            } else {
                // Handle error
                die('Something went wrong.');
            }
        } else {
            // If validation fails, redirect back to create form
            // In a prod environment, you'd show an error message.
            header('Location: /posts/create');
            exit();
        }
    }

    /**
     * Show the form for editing an existing post
     */
    public function edit($id)
    {
        $post = $this->postModel->getPostById($id);

        // IN a production app, you'd check if the user is authorized to edit this post.

        if (!$post) {
            // Post not found, handle error (e.g., show 404 page)
            die('Post not found.');
        }

        $pageTitle = 'Edit Post';
        require_once '../app/Views/posts/edit.php';
    }

    /**
     * Update an existing post in the database.
     */
    public function update($id)
    {
        // Basic validation TODO: Strengthen this
        if (isset($_POST['title']) && isset($_POST['body']) && !empty($_POST['title']) && !empty($_POST['body'])) {
            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'body' => trim($_POST['body'])
            ];

            // Sanitize data
            $data['title'] = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
            $data['body'] = htmlspecialchars($data['body'], ENT_QUOTES, 'UTF-8');

            if ($this->postModel->updatePost($data)) {
                // Redirect to the post's page on success
                header('Location: /posts/show/' . $id);
                exit();
            } else {
                die('Something went wrong.');
            }
        } else {
            // If validation fails, redirect back to edit form
            header('Location: /posts/edit/' . $id);
            exit();
        }
    }
}
