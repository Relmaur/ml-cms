<?php

declare(strict_types=1);

namespace App\Models;

use Core\Http\Request;
use Core\Model;
use Core\Session;

/**
 * Post Model
 * 
 * Represents a blog post in the database.
 * 
 * Thanks to the base Mode class, we get these methods fro FREE:
 * - Post::all()
 * - Post::find($id)
 * - Post::create($data)
 * - Post::where(...)->get()
 * - $post->save()
 * - $post->delete()
 * 
 * We only need to define:
 * 1. The table name
 * 2. Which columns can be mass-assigned (security)
 * 3. Any custom query methods specific to posts
 */
class Post extends Model
{

    /**
     * The database table name
     */
    protected $table = 'posts';

    /**
     * COlumns that can be mass-assigned
     * 
     * This protects agains mass asignment vulnerabilities.
     * Only these columns can be set via Posts::create() or $post->gfill()
     * 
     * Example attack without $fillable:
     * Post::create($_POST) // User could inject is_admin = 1!
     * 
     * With $fillable, only these columns are allowed.
     */
    protected $fillable = [
        'title',
        'content',
        'author_id',
        'image_path',
        'status'
    ];

    /**
     * Enable automatic created_at and updated_at timestamps
     */
    protected $timestamps = true;

    /**
     * ====================
     * CUSTOM QUERY METHODS
     * ====================
     * 
     * The base Model class handles basic CRUD.
     * Add methods here for custom queries specific to posts.
     */

    /**
     * Get all posts with author information
     * 
     * This is a custom query that the base class doesn't provide.
     * It joins with the users table to get author names.
     * 
     * @return array
     */
    public static function allWithAuthors(): array
    {
        return self::$db->table('posts')
            ->leftJoin('users', 'posts.author_id', '=', 'users.id')
            ->select(['posts.*', 'users.name as author_name'])
            ->orderBy('posts.created_at', 'desc')
            ->get();
    }

    /**
     * Get a post with author information
     * 
     * @param int $id
     * @return object|null
     */
    public static function findWithAuthor($id)
    {
        return self::$db->table('posts')
            ->leftJoin('users', 'posts.author_id', '=', 'users.id')
            ->select('posts.*', 'users.name as author_name')
            ->where('posts.id', $id)
            ->first();
    }

    /**
     * Get published posts only
     * 
     * @return array
     */
    public static function published(): array
    {
        return static::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get posts by a specific author
     * 
     * @param int $authorId
     * @return array
     */
    public static function byAuthor($authorId)
    {
        return static::where('author_id', $authorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Search posts by title or content
     * 
     * @param string $query
     * @return array
     */
    public static function search($query)
    {
        return static::where('title', 'LIKE', '%', $query . '%')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the latest posts
     * 
     * @param int $limit
     * @return array
     */
    public static function latest($limit = 10)
    {
        return static::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Save the post
     * 
     * @param Request $data The data coming from the request
     */
    public static function createPost(Request $request)
    {
        $title = $request->input('title') ?: '';
        $content = $request->input('content') ?: '';
        $author_id = Session::get('user_id');

        return static::create([
            'title' => $title,
            'content' => $content,
            'author_id' => $author_id
        ]);
    }
}
