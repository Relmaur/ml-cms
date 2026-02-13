<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Post;
use Core\Session;
use Core\View;
use Core\Cache;
use Core\Http\HtmlResponse;
use Core\Http\RedirectResponse;
use Core\Http\Request;

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
        $cacheKey = 'posts.all';

        // Try to get the posts from the cache first
        $posts = Cache::get($cacheKey);

        if (!$posts) {
            // If not in cache, get from the database
            $posts = $this->postModel->getAllPosts();

            // Store the result in the cache for next time (e.g., for 10 minutes)
            Cache::put($cacheKey, $posts, 10);
        }

        $pageTitle = $posts ? 'All Posts' : 'No Posts Found';

        return View::render('posts/index', [
            'posts' => $posts,
            'pageTitle' => $pageTitle
        ]);
    }

    /**
     * Show a single post.
     */

    public function show($id)
    {

        $cacheKey = 'post.' . $id;

        $post = Cache::get($cacheKey);

        if (!$post) {
            $post = $this->postModel->getPostById($id);
            Cache::put($cacheKey, $post, 10);
        }

        $pageTitle = $post ? $post->title : 'Post Not Found';

        return View::render('posts/show', [
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
        // $this->requireAuth();

        $pageTitle = 'Create New Post';

        return View::render('posts/create', [
            'pageTitle' => $pageTitle
        ]);
    }

    /**
     * Store a new post in the database
     */
    public function store(Request $request)
    {

        // Middleware-like check that the BaseController class provides
        // $this->requireAuth();

        // Basic validation
        if ($request->has('title') && $request->has('content') && !empty($request->input('title')) && !empty($request->input('content'))) {

            // Image handling
            $imagePath = null;
            // Check if an image was uploaded
            if ($request->hasFile('image')) {
                $image = $request->file('image');

                // File validation
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($image['type'], $allowedTypes)) {
                    die("Invalid file type. Please upload a JPG, PNG, or GIF");
                }

                if ($image['size'] > 2000000) { // 2MB limit
                    die("File is too large. Maximum size is 2MB.");
                }

                // Sanitize filename and create a unique name
                $name = pathinfo($image['name'], PATHINFO_FILENAME);
                $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
                $safeName = preg_replace('/[^A-Za-z0-9_-]/', '', $name);
                $uniqueName = $safeName . '_' . time() . '.' . $extension;

                $uploadDir = __DIR__ .  "/../../public/uploads/";
                $imagePath = '/uploads/' . $uniqueName;

                if (!move_uploaded_file($image['tmp_name'], $uploadDir . $uniqueName)) {
                    die("Failed to move uploaded file.");
                }
            }

            $data = [
                'title' => trim($request->input('title')),
                'content' => trim($request->input('content')),
                'author_id' => Session::get('user_id'),
                'image_path' => $imagePath,
            ];

            // Sanitize data before inserting (!importing)
            $data['title'] = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
            $data['content'] = htmlspecialchars($data['content'], ENT_QUOTES, 'UTF-8');

            if ($this->postModel->createPost($data)) {

                Cache::forget('posts.all');
                Session::flash('success', 'Post created successfully!');
                
                // Redirect to the blog index on success
                return new RedirectResponse('/posts');
            }
        } else {

            Session::flash('error', 'There was an error creating the post');
            // If validation fails, redirect back to create form
            // In a prod environment, you'd show an error message.
            return new RedirectResponse('/posts/create');
        }
    }

    /**
     * Show the form for editing an existing post
     */
    public function edit($id)
    {

        // Middleware-like check that the BaseController class provides
        // $this->requireAuth();

        $post = $this->postModel->getPostById($id);

        // IN a production app, you'd check if the user is authorized to edit this post.

        if ($post->author_id !== Session::get('user_id')) {
            // Post not found, handle error (e.g., show 404 page)
            http_response_code(403);
            die('You are not authorized to edit this post.');
        }

        $pageTitle = 'Edit Post';

        return View::render('posts/edit', [
            'pageTitle' => $pageTitle,
            'post' => $post
        ]);
    }

    /**
     * Update an existing post in the database.
     */
    public function update($id, Request $request)
    {

        // Middleware-like check that the BaseController class provides
        // $this->requireAuth();

        // Basic validation TODO: Strengthen this
        if ($request->has('title') && $request->has('content') && !empty($request->input('title')) && !empty($request->input('content'))) {

            $imagePath = $this->postModel->getPostById($id)->image_path;

            // Check if an image was uploaded
            if ($request->hasFile('image') && $request->file('image')) {

                $image = $request->file('image');

                // File validation
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($image['type'], $allowedTypes)) {
                    die("Invalid file type. Please upload a JPG, PNG, or GIF");
                }

                if ($image['size'] > 2000000) { // 2MB limit
                    die("File is too large. Maximum size is 2MB.");
                }

                // Sanitize filename and create a unique name
                $name = pathinfo($image['name'], PATHINFO_FILENAME);
                $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
                $safeName = preg_replace('/[^A-Za-z0-9_-]/', '', $name);
                $uniqueName = $safeName . '_' . time() . '.' . $extension;

                $uploadDir = __DIR__ .  "/../../public/uploads/";
                $imagePath = '/uploads/' . $uniqueName;

                if (!move_uploaded_file($image['tmp_name'], $uploadDir . $uniqueName)) {
                    die("Failed to move uploaded file.");
                }
            }

            $data = [
                'id' => $id,
                'title' => trim($request->input('title')),
                'content' => trim($request->input('content')),
                'image_path' => $imagePath,
            ];

            // Sanitize data
            $data['title'] = e($data['title'], ENT_QUOTES, 'UTF-8');
            $data['content'] = e($data['content'], ENT_QUOTES, 'UTF-8');

            if ($this->postModel->updatePost($data)) {

                Cache::forget('posts.all');
                Cache::forget('post.' . $id);

                Session::flash('success', 'Post updated successfully');

                // Redirect to the post's page on success
                return new RedirectResponse('/posts/' . $id);
            }
        } else {
            // If validation fails, redirect back to edit form
            return new RedirectResponse('/posts/' . $id . '/edit');
        }
    }

    /**
     * Delete a post.
     */
    public function destroy($id)
    {

        // Middleware-like check that the BaseController class provides
        // $this->requireAuth();

        if ($this->postModel->deletePost($id)) {

            Cache::forget('posts.all');
            Cache::forget('post.' . $id);

            Session::flash('success', 'Post deleted successfully');

            // Redirect to the blog index on success
            return new RedirectResponse('/posts');
        }
    }
}
