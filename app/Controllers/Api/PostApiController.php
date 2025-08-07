<?php

namespace App\Controllers\Api;

use App\Models\Post;

class PostApiController extends ApiController
{
    private $postModel;

    public function __construct(Post $postModel)
    {
        $this->postModel = $postModel;
    }

    /**
     * Get all posts.
     */
    public function index()
    {
        $posts = $this->postModel->getAllPosts();
        $this->jsonResponse($posts);
    }

    /**
     * Get a  single post by ID.
     */
    public function show($id)
    {
        $post = $this->postModel->getPostById($id);

        if ($post) {
            $this->jsonResponse($post);
        } else {
            // Send a 404 Not Found response if the post doesn't exist
            $this->jsonResponse(['message' => 'Post not found'], 404);
        }
    }
}
