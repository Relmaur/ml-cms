<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Post;
use Core\Session;
use Core\View;

class PostsController extends BaseController
{
    protected $postModel;

    public function __construct(Post $postModel)
    {
        // To be able to access requireAuth middleware
        parent::__construct();
        $this->postModel = $postModel;
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

        View::render('posts/index', [
            'posts' => $posts,
            'pageTitle' => 'All Posts'
        ]);
    }

    /**
     * Show a single post.
     */

    public function show($id)
    {
        $post = $this->postModel->getPostById($id);
        $pageTitle = $post ? $post->title : 'Post Not Found';

        View::render('posts/show', [
            'post' => $post,
            'pageTitle' => $pageTitle
        ]);
    }

    /**
     * MARK: Protected Routes start here
     */

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        // Middleware-like check that the BaseController class provides
        $this->requireAuth();

        $pageTitle = 'Create New Post';

        View::render('posts/create', [
            'pageTitle' => $pageTitle
        ]);
    }

    /**
     * Store a new post in the database
     */
    public function store()
    {

        // Middleware-like check that the BaseController class provides
        $this->requireAuth();

        // Basic validation
        if (isset($_POST['title']) && isset($_POST['content']) && !empty($_POST['title']) && !empty($_POST['content'])) {
            $data = [
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'author_id' => Session::get('user_id'),
            ];

            // Sanitize data before inserting (!importing)
            $data['title'] = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
            $data['content'] = htmlspecialchars($data['content'], ENT_QUOTES, 'UTF-8');

            if ($this->postModel->createPost($data)) {

                Session::flash('success', 'Post created successfully!');
                // Redirect to the blog index on success
                header('Location: /posts');
                exit();
            } else {
                // Handle error
                die('Something went wrong.');
            }
        } else {

            Session::flash('error', 'There was an error creating the post');
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

        // Middleware-like check that the BaseController class provides
        $this->requireAuth();

        $post = $this->postModel->getPostById($id);

        // IN a production app, you'd check if the user is authorized to edit this post.

        if ($post->author_id !== Session::get('user_id')) {
            // Post not found, handle error (e.g., show 404 page)
            http_response_code(403);
            die('You are not authorized to edit this post.');
        }

        $pageTitle = 'Edit Post';

        View::render('posts/edit', [
            'pageTitle' => $pageTitle,
            'post' => $post
        ]);
    }

    /**
     * Update an existing post in the database.
     */
    public function update($id)
    {

        // Middleware-like check that the BaseController class provides
        $this->requireAuth();

        // Basic validation TODO: Strengthen this
        if (isset($_POST['title']) && isset($_POST['content']) && !empty($_POST['title']) && !empty($_POST['content'])) {
            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content'])
            ];

            // Sanitize data
            $data['title'] = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
            $data['content'] = htmlspecialchars($data['content'], ENT_QUOTES, 'UTF-8');

            if ($this->postModel->updatePost($data)) {

                Session::flash('success', 'Post updated successfully');
                // Redirect to the post's page on success
                header('Location: /posts/' . $id);
                exit();
            } else {
                die('Something went wrong.');
            }
        } else {
            // If validation fails, redirect back to edit form
            header('Location: /posts/' . $id . 'edit/');
            exit();
        }
    }

    /**
     * Delete a post.
     */
    public function destroy($id)
    {

        // Middleware-like check that the BaseController class provides
        $this->requireAuth();

        if ($this->postModel->deletePost($id)) {

            Session::flash('success', 'Post deleted successfully');
            // Redirect to the blog index on success
            header('Location: /posts');
            exit();
        } else {
            die('Something went worng.');
        }
    }
}
