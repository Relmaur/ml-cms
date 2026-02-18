<?php

declare(strict_types=1);

namespace APp\Models;

use App\Models\Post;
use Core\Model;

/**
 * User Model
 * 
 * Represents a user account in the database.
 */
class User extends Model
{

    /**
     * The database table name
     */
    protected $table = 'users';

    /**
     * Columns that can be mass-assigned
     */
    protected $fillable = [
        'name',
        'email'
    ];

    /**
     * Enable automatic timestamps
     */
    protected $timestamps = true;

    /**
     * ===============
     * CUSTOM MOETHODS
     * ===============
     */

    /**
     * Find a user by email address
     * 
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }

    /**
     * Create a new user with hashed password
     * 
     * Convenience method that handles password hashing automatically.
     * 
     * Usage:
     * User::register([
     *  'name' => 'John Doe',
     *  'email' => 'john@example.com',
     *  'password' => 'secret123'
     * ]);
     * 
     * @param array $data Must include 'password' key
     * @return static
     */
    public static function register(array $data)
    {
        // Hash the password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return static::create($data);
    }

    /**
     * Verify a user's password
     * 
     * Usage:
     * $user = User::findByEmail($email);
     * if($user && $user->verifyPassword($password)) {
     *  // Login success
     * }
     * 
     * @param string $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Get all posts by this user
     * 
     * This demonstrates a relationship method.
     * 
     * @return array
     */
    public function posts()
    {
        return Post::where('author_id', $this->id)->get();
    }

    /**
     * Update the user's password
     * 
     * Handles password hashing automatically.
     * 
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($newPassword)
    {
        $this->password = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->save();
    }
}