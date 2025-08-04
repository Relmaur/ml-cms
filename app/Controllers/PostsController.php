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
            $data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
            $data['body'] = filter_var($data['body'], FILTER_SANITIZE_STRING);

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
}
