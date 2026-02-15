<?php

declare(strict_types=1);

namespace Core;

use Core\Database;

/**
 * Base Model Class (Active Record Pattern)
 * 
 * All models should extend this class to get free CRUD methods.
 * 
 * How to use:
 * 1. Create a model class that extends Model
 * 2. Set the $table property
 * 3. Optionally set $fillable for mass assignment
 * 
 * Example:
 * class Post extends Model {
 *  protected $tbale = 'posts';
 *  protected $fillable = ['title', 'content', 'author_id']; 
 * }
 * 
 * Then you can use:
 * Post::all()
 * Post::find(5)
 * Post::where('status', 'published')
 * $post = new Post();
 * $post->title = 'My Post';
 * $post->save();
 * $post->delete();
 */
abstract class Model
{
    /**
     * The database table name
     * Child classes MUST set this property
     * 
     * Example: protected $table = 'posts'
     */
    protected $table;

    /**
     * The imprimary key column name
     * Default: 'id'
     * 
     * Override if your table uses a different primary key:
     * protected $primaryKey = 'post_id';
     */
    protected $primaryKey = 'id';

    /**
     * Columns that can be mass-assigned
     * Protectes agains mass assignment vulnerabilities
     * 
     * Example: protected $fillable = ['title', 'content', 'author_id'];
     * 
     * If empty, all columns can be mass-assigned (less secure)
     */
    protected $fillable = [];

    /**
     * Whether to automatically manage created_at and updated_at
     * Set to false if your table doesn't have these columns
     */
    protected $timestamps = true;

    /**
     * The model's attributes (column => table)
     * This is the data for this specific row
     * 
     * Example: ['id' => 5, 'title' => 'My Post', 'content' => '...']
     */
    protected $attributes = [];

    /**
     * Track which attributes have been modified
     * Used to only update changed fields
     */
    protected $dirty = [];

    /**
     * WHether this model exists in the database
     * True if loaded from DB or saved, false if new instance
     */
    protected $exists = false;

    /**
     * Database instance (shared across all models)
     */
    protected static $db;

    /**
     * Constructor
     * 
     * Can optionally pass attribtues to pre-populate the model:
     * $post = new Post(['title' => 'My Post', 'content' => 'Hello']);
     * 
     * @param array $attributes
     */
    public function __construct(array $attributes = []) {

    // Get database instance (singleton)
    
    }
}
