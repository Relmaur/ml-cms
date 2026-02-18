<?php

declare(strict_types=1);

namespace Core;

use Core\Database;
use Core\ModelQueryBuilder;

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
 *  protected $table = 'posts';
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
    public function __construct(array $attributes = [])
    {

        // Get database instance (singleton)
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }

        // Fill attributes if provided
        if (!empty($attributes)) {
            $this->fill($attributes);
        }
    }

    /**
     * ========================================
     * STATIC QUERY METHODS (Work on the table)
     * ========================================
     */

    /**
     * Get a new query builder instance for this model's table
     * 
     * This is the magic that makes Post::where() work!
     * 
     * Usage:
     * Post::query()->where('status', 'published')->get();
     * 
     * @return \Core\Database\QueryBuilder
     */
    public static function query()
    {
        $model = new static();
        $queryBuilder = self::$db->table($model->table);
        return new ModelQueryBuilder($queryBuilder, static::class);
    }

    /**
     * Get all records
     * 
     * Usage:
     * $posts = Post::all();
     * 
     * @return array Array of model instances
     */
    public static function all()
    {
        $model = new static();
        $results = self::query()->get();
        return self::hydrate($results);
    }

    /**
     * Find a record by ID
     * 
     * Usage:
     * $post = Post::find(5);
     * 
     * @param mixed $id
     * @return static|null Model instance or null
     */
    public static function find($id)
    {
        $model = new static();
        $result = self::query()->where($model->primaryKey, $id);

        if (!$result) {
            return null;
        }

        return self::hydrateOne($result);
    }

    /**
     * Find a record by ID or throw an exception
     * 
     * Useful when you expect the record to exist
     * 
     * Usage:
     * $post = Post::findorFail(5);
     * 
     * @param mixed $id
     * @return static
     * @throws \Exception
     */
    public static function findOrFail($id)
    {
        $result = static::find($id);

        if ($result === null) {
            $model = new static();
            throw new \Exception("No record found in table '{$model->table}' with ID {$id}");
        }
    }

    /**
     * Create a new record in the database
     * 
     * This is a convenience method that creates AND saves in one step.
     * 
     * Usage:
     * $post = Post::create([
     *  'title' => 'My Post',
     *  'content' => 'Post content',
     *  'author_id' => 1
     * ]);
     * 
     * @param array $attributes
     * @return static The created model instance
     */
    public static function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Magic method to forward static calls to the query builder
     * 
     * This is what makes Post::where(), Post::orderBy(), etc. work!
     * 
     * When you call Post::where('status', 'published'), PHP:
     * 1. Sees Post doesn't have a static where() method
     * 2. Calls this __callStatic() method
     * 3. We forward it to the query builder
     * 
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        // Get a query builder instance and call the method on it
        return call_user_func_array([static::query(), $method], $parameters);
    }

    /**
     * ======================================
     * INSTANCE METHODS (Work on a single row)
     * ======================================
     */

    /**
     * Fill the model with an array of attributes
     * 
     * Respects $fillable if set(mass assignment protection)
     * 
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {

            // If $fillable is set, only allow those columns
            if (!empty($this->fillable) && !in_array($key, $this->fillable)) {
                continue;
            }

            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Save the model to the database
     * 
     * Automatically determines whether to INSERT or UPDATE based on
     * whether the model exists in the database.
     * 
     * Usage:
     * $post = new Post();
     * $post->title = 'My Post';
     * $post->save(); // INSERT
     * 
     * $post->title = 'Update';
     * $post->save();
     * 
     * @return bool Success
     */
    public function save()
    {
        // Add timestamps if enables
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');

            if (!$this->exists) {
                // Creating - set both create_at and updated_at
                $this->setAttribute('created_at', $now);
                $this->setAttribute('updated_at', $now);
            } else {
                // Updating - only set updated_at
                $this->setAttribute('updated_at', $now);
            }
        }

        // Determine INSERT or UPDATE
        if ($this->exists) {
            return $this->performUpdate();
        } else {
            return $this->performInsert();
        }
    }

    /**
     * Perform an INSERT query
     * 
     * @return bool
     */
    protected function performInsert()
    {

        // Insert all attributes
        $id = self::$db->table($this->table)->insert($this->attributes);

        // Set the primary kye
        $this->setAttribute($this->primaryKey, $id);

        // Mark as existing
        $this->exists = true;

        // Clear dirty tracking
        $this->dirty = [];

        return true;
    }

    /**
     * Perform an UPDATE query
     * 
     * Only updates attributes that have been modified (dirty tracking)
     * 
     * @return bool
     */
    protected function performUpdate()
    {
        // If nothing changed, don't update
        if (empty($this->dirty)) {
            return true;
        }

        // Get only the changed attributes
        $data = array_intersect_key($this->attributes, array_flip($this->dirty));

        // Update in database
        self::$db->table($this->table)
            ->where($this->primaryKey, $this->attributes[$this->primaryKey])
            ->update($data);

        // Clear dirty tracking
        $this->dirty = [];

        return true;
    }

    /**
     * Delete the model from the database
     * 
     * Usage:
     * $post = Post::find(5);
     * $post->delete();
     * 
     * @return bool
     * @throws \Exception If model doesn't exist
     */
    public function delete()
    {
        if (!$this->exists) {
            throw new \Exception('Cannot delete a model that does not exists in the database.');
        }

        self::$db->table($this->table)
            ->where($this->primaryKey, $this->attributes[$this->primaryKey])
            ->delete();

        $this->exists = false;
        return true;
    }

    /**
     * Refresh the model from the database
     * 
     * Useful if you think the data might h ave change
     * 
     * Usage:
     * $post = Post::find(5);
     * // ... some time passes, another process updates the post
     * $post->refresh(); // Reload fresh data from database
     * 
     * @return $this;
     */
    public function refresh()
    {
        if (!$this->exists) {
            return $this;
        }

        $fresh = static::find($this->attributes[$this->primaryKey]);

        if ($fresh) {
            $this->attributes = $fresh->attributes;
            $this->dirty = [];
        }

        return $this;
    }

    /**
     * ====================================
     *  ATTRIBUTE ACCESSORS (magic methods)
     * ====================================
     */

    /**
     * Get an attribute value
     * 
     * This enables: $post->title (instead of $post->getAttributes('title'))
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Set an attribute value
     * 
     * This enables: $post->title = 'New Title' (instead of $post->setAttribute('title', 'New Title'))
     * 
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Check if an attribute exists
     * 
     * This enables: isset($post->title)
     * 
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Get an attribute value
     * 
     * @param string $key
     * @return mixed
     */
    public function getattribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return null;
    }

    /**
     * Set an attribute value
     * 
     * Also tracks which attributes have changed (dirty tracking)
     * 
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {

        // Track as dirty if value changed
        if (!array_key_exists($key, $this->attributes) || $this->attributes[$key] !== $value) {
            $this->dirty[] = $key;
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get all attributes as an array
     * 
     * Usage:
     * $data = $post->toArray();
     * // ['id' => 5, 'title' => 'My Post', ...]
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convert the model to JSON
     * 
     * Useful dor API responses
     * 
     * Usage:
     * echo $post->toJson();
     * // {"id"":5,"title":"My Post",...}
     * 
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->attributes);
    }

    /**
     * ==========================================
     * HYDRATION (Convert query results to models)
     * ==========================================
     */

    /**
     * Convert an array of query results into model instances
     * 
     * This is what converts raw database rows into Post objects
     * 
     * @param array $results Array of stdClass objects from database
     * @return array Array of Model instances
     */
    protected static function hydrate(array $results)
    {
        $models = [];

        foreach ($results as $result) {
            $models[] = self::hydrateOne($result);
        }

        return $models;
    }

    /**
     * Convert a single query result into a model instance
     * 
     * @param object $result A stdClass object from database
     * @return static Model instance
     */
    protected static function hydrateOne($result)
    {
        $model = new static();

        // Convert stdClass to array
        $attributes = (array) $result;

        // Set all attributes
        foreach ($attributes as $key => $value) {
            $model->attributes[$key] = $value;
        }

        // Mark as existing (loaded from database)
        $model->exists = true;

        // Nothing is dirty yet
        $model->dirty = [];

        return $model;
    }

    /**
     * Create a model instance from a raw database row
     *
     * Public entry point used by ModelQueryBuilder for hydration.
     *
     * @param object $row A stdClass object from the database
     * @return static
     */
    public static function newFromRow($row)
    {
        return static::hydrateOne($row);
    }

    /**
     * ==============
     * HELPER METHODS
     * ==============
     */

    /**
     * Get the table name
     * 
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the primary key name
     * 
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the primary key value
     * 
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->primaryKey);
    }

    /**
     * Check if the model exists in the database
     * 
     * @return bool
     */
    public function exists()
    {
        return $this->exists;
    }

    /**
     * Check if any attributes have been modified
     * 
     * @return bool
     */
    public function isDirty()
    {
        return !empty($this->dirty);
    }

    /**
     * Get the attribute that have been modified
     * 
     * @return array
     */
    public function getDirty()
    {
        return array_intersect_key($this->attributes, array_flip($this->dirty));
    }
}