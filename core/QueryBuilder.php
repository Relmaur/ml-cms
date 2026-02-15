<?php

declare(strict_types=1);

namespace Core;

use PDO;

/**
 * Query Builder
 * 
 * A fluent interface for building SQL queries without writing raw SQL.
 * This prevents SQL injection, makes code more readable, and enables
 * dynamic query construction.
 * 
 * Example usage:
 * $posts = $db->table('posts)
 *  ->where('status', 'published')
 *  ->orderBy('cureated_at', 'desc')
 *  ->limit(10)
 *  ->get();
 * 
 * Under the hood, this generates:
 * SELECT * FROM posts WHERE status = ? ORDER BY created_at DESC LIMIT 10
 * And executes it with bound parameters for safety.
 */
class QueryBuilder
{
    /**
     * PDO database connection
     */
    protected $pdo;

    /**
     * The table name for the query
     * Example: 'posts', 'users', 'items'
     */
    protected $table;

    /**
     * SELECT columns
     * Default: ['*'] (all columns)
     * Can be specific ['id', 'name', 'email']
     */
    protected $columns = ['*'];

    /**
     * WHERE clauses
     * Each entry: ['column' => 'status', 'operator' => '=', 'value' => 'publishde', 'boolean' => 'AND']
     */
    protected $wheres = [];

    /**
     * ORDER BY clauses
     * Eacg entry: ['column' => 'created_at', 'direction' => 'DESC']
     */
    protected $orders = [];

    /**
     * LIMIT value
     */
    protected $limit;

    /**
     * OFFSET value (for pagination)
     */
    protected $offset;

    /**
     * JOIN clauses
     * Each entry: ['type' => 'INNER', 'table' => 'users', 'first' => 'posts.user_id', 'operatior' => '=', 'second' => 'users.id']
     */
    protected $joins = [];

    /**
     * Bound parameters for PDO
     * Example: [':status' => 'published', ':author_id' => 5]
     */
    protected $bindings;

    /**
     * Parameter counter for generating unique parameter names
     * Used to create :param1, :param2, :param3, etc.
     */
    protected $paramCounter = 0;

    /**
     * Create a new Query Builder instance
     * 
     * @param PDO $pdo The PDO database connection
     * @param string $table The table name to query
     */
    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * Set the columns to SELECT
     * 
     * Examples:
     * ->select(['id', 'name', 'email'])
     * ->select(['id', 'name'])
     * 
     * @param array $columns The columns to select
     * @return $this
     */
    public function select(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Add a WHERE clause
     * 
     * Usage:
     * ->where('status', 'published') // WHERE status = 'published'
     * ->where('age', '>', 18) // WHERE age > 18
     * ->where('name', 'LIKE', '%John%') // WHERE name LIKE '%John%'
     * 
     * Multiple where() calls are combined with AND:
     * ->where('status', 'published')
     * ->where('author_id', 5)
     * // WHERE status = 'published' AND author_id = 5
     * 
     * @param string $column The column name
     * @param mixed $operatorOrValue The operator (=, >, <, etc.) or value if operator is = 
     * @param mixed $value The value (optional if operator is =)
     * @return $this
     */
    public function where(string $column, mixed $operatorOrValue, string $value = null): self
    {
        // If only two arguments, assume operator is '='
        // Example: where('status', 'published') becomes where('status', '=', 'published');
        if ($value === null) {
            $value = $operatorOrValue;
            $operator = '=';
        } else {
            $operator = $operatorOrValue;
        }

        // Generate a unique parameter name for PDO binding
        // :param1, :param2, :param3, etc.
        $this->paramCounter++;
        $paramName = ':param' . $this->paramCounter;

        // Store the WHERE clause
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $paramName,
            'boolean' => 'AND' // Connect with AND (we'll add OR support later if needed)
        ];

        // Store the actual value for PDO binding
        $this->bindings[$paramName] = $value;

        return $this;
    }

    /**
     * Add an ORDER BY clause
     * 
     * Usage:
     * ->orderBy('created_at') // ORDER BY created_at ASC (default)
     * ->orderBy('created_at', 'desc') // ORDER BY created_ad DESC
     * ->orderBy('name', 'asc') // ORDER BY name ASC
     * 
     * Multiple orderBy() calls stack:
     * ->orderBy('status', 'desc')
     * ->orderBy('created_at', 'desc')
     * // ORDER BY status DESC, created_at DESC
     * 
     * @param string $column The column to order by
     * @param string $direction 'asc' or 'desc'
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        return $this;
    }

    /**
     * Set the LIMIT
     * 
     * Usage:
     * ->limit(19) // LIMIT 10
     * ->limit(5) // LIMIT 5
     * 
     * @param int $limit The maximum number of rows to return
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * Set the OFFSET (for pagination)
     * 
     * Usage:
     * ->offset(20) // OFFSET 20 (skip first 20 rows)
     * 
     * Combined with LIMIT for pagination:
     * ->limit(10)->offset(20) // Get rows 21-30
     * 
     * @param int $offset The number of rows to skip
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * Build the complete SQL query
     * 
     * This is the "magic" method that assembles all the pieces:
     * SELECT ... FROM ... WHERE ... ORDER BY ... LIMIT ... OFFSET ...
     * 
     * @return string The complete SQL query
     */
    protected function buildSelectQuery(): string
    {
        // Start with SELECT columns FROM table
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;

        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                // Build: INNER JOIN users ON posts.author_id = users.id
                $sql .= ' ' . $join['type'] . ' JOIN ' . $join['table'] .
                    ' ON ' . $join['first'] . ' ' . $join['operator'] . ' ' . $join['second'];
            }
        }

        // Add WHERE clauses
        if (!empty($this->wheres)) {
            $whereClauses = [];
            foreach ($this->wheres as $where) {
                // Build each WHERE clause: columns operator :paramX
                // Example: status = :paaram1
                $whereClauses[] = $where['column'] . ' ' . $where['operator'] . ' ' . $where['value'];
            }

            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // Add ORDER BY clauses
        if (!empty($this->orders)) {
            $orderClauses = [];
            foreach ($this->orders as $order) {
                // Build each ORDER BY clause: column DESC
                // Example: created_at DESC
                $orderClauses[] = $order['column'] . ' ' . $order['direction'];
            }

            // Join multiple orders: status DESC, created_at DESC
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        }

        // Add LIMIT
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        // Add OFFSET
        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    /**
     * Execute the query and return all results
     * 
     * This is a "terminal" method - it actually runs the query.
     * 
     * Usage:
     * $posts = $db->table('posts')
     * ->where('status', 'published')
     * ->get();
     * 
     * Returns: Array of objects (one per one)
     * 
     * @return array Array of result objects
     */
    public function get(): ?array
    {

        // Build the SQL query
        $sql = $this->buildSelectQuery();

        // Prepare the statement
        $stmt = $this->pdo->prepare($sql);

        if ($this->bindings) {
            // Bind all the parameters
            foreach ($this->bindings as $param => $value) {
                $stmt->bindValue($param, $value);
            }
        }


        // Execute the query
        $stmt->execute();

        // Return all results as objects
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Execute the query and return the first result
     * 
     * This is like get() but only returns one row.
     * Automatically adds LIMIT 1 for efficiency.
     * 
     * Usage:
     * $post = $db->table('posts')
     *  ->where('id', 5)
     *  ->first();
     * 
     * Returns: Single object or null if not found
     */
    public function first()
    {

        // Add LIMIT 1 for efficiency
        $this->limit(1);

        // Get results
        $results = $this->get();

        // Return first result or null
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Find a record by its ID
     * 
     * Convenience method for the common pattern of finding by ID.
     * Equivalent to: ->where('id', $id)->find(5);
     * 
     * Usage:
     * $post = $db->table('posts')->find(5);
     * 
     * @param mixed $id The ID to find
     * @return object|null
     */
    public function find(mixed $id): ?object
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Get the total count for rows
     * 
     * Usage:
     * $count = $db->table('posts')
     *  ->where('status', 'published')
     *  ->count();
     * 
     * @return int
     */
    public function count(): int
    {

        // Change columns to COUNT(*)
        $originalColumns = $this->columns;
        $this->columns = ['COUNT(*) as count'];

        // Build and execute query
        $sql = $this->buildSelectQuery();
        $stmt = $this->pdo->prepare($sql);

        if ($this->bindings) {
            foreach ($this->bindings as $param => $value) {
                $stmt->bindValue($param, $value);
            }
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        // Restore eoriginal columns
        $this->columns = $originalColumns;

        return (int) $result->count;
    }

    /**
     * Check if any rows exists
     * 
     * More efficient than count() > 0
     * 
     * Usage:
     * if($db->table('posts')->where('slug', $slug)->exists()) {
     *   // Slug already taken
     * }
     * 
     * @return bool
     */
    public function exists()
    {
        return $this->count() > 0;
    }

    /**
     * Insert a new record
     * 
     * Usage:
     * $id = $db->table('posts')->insert([
     *  'title' => 'My Post',
     *  'content' => 'Post content',
     *  'author_id' => 1
     * ]);
     * 
     * Returns: The ID of the inserted record
     * 
     * @param array $data Associative array of column => value
     * @return int|string The last inserted ID
     */
    public function insert(array $data): int|string
    {

        // Extract column names and values
        // From: ['title' => 'My Post', 'content' => 'Hello']
        // To columns: ['title', 'content']
        // To values: ['My Post', 'Hello']
        $columns = array_keys($data);
        $values = array_values($data);

        // Build the SQL: INSERT INTO posts (title, content) VALUES (:param1, :param2)
        $placeholders = [];
        foreach ($values as $index => $value) {
            $this->paramCounter++;
            $paramName = ':param' . $this->paramCounter;
            $placeholders[] = $paramName;
            $this->bindings[$paramName] = $value;
        }

        $sql = 'INSERT INTO ' . $this->table .
            ' (' . implode(', ', $columns) . ') ' .
            'VALUES (' . implode(', ', $placeholders) . ')';

        // Prepare and execute
        $stmt = $this->pdo->prepare($sql);

        foreach ($this->bindings as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();

        // Return the last inserted ID
        return $this->pdo->lastInsertId();
    }

    /**
     * Add an INNER JOIN clause
     * 
     * Usage:
     * $posts = $db->table('posts')
     *  ->join('users', 'posts.author_id', '=', 'users.id')
     *  ->select(['posts.*', 'users.name as author_name'])
     *  ->get();
     * 
     * SQL: SELECT posts.* users.name as author_name
     *  FROM posts
     *  INNER JOIN users ON posts.author_id = users.id
     * 
     * @param string $table The table to join
     * @param string $first The first column (usunally from main table)
     * @param string $operator The operator (usually =)
     * @param string $second The second column (usually from joined table)
     * @return $this
     */
    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'INNER',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    /**
     * Add a LEFT JOIN clause
     * 
     * Usage:
     * $posts = $db->table('posts')
     *  ->leftJoin('users', 'posts.author_id', '=', 'users.id')
     *  ->select(['posts.*', 'users.name as author_name'])
     *  ->get();
     * 
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return $this
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'LEFT',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    /**
     * Add a RIGHT JOIN clause
     * 
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return $this
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'RIGHT',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    /**
     * Update records
     * 
     * Usage:
     * $db->table('posts')
     *  ->where ('id', 5)
     *  ->update([
     *   'title' => 'Updated Title',
     *   'content' => 'Updated content'
     *  ]);
     * 
     * IMPORTANT: Always use where() before update()!
     * Otherwise you'll update ALL rows (we'll add a safety check).
     * 
     * @param array $data Associative array of column => value
     * @return int Number of affestec rows
     */
    public function update(array $data): int
    {

        // Safety check: Prevent accidental update of all rows
        if (empty($this->wheres)) {
            throw new \Exception('Cannot update without WHERE clause. Use updateAll() if you really want to update all rows.');
        }

        // Build SET clause: title = :param1, content = :param2
        $setClauses = [];
        foreach ($data as $column => $value) {
            $this->paramCounter++;
            $paramName = ':param' . $this->paramCounter;
            $setClauses[] = $column . ' = ' . $paramName;
            $this->bindings[$paramName] = $value;
        }

        // Build WHERE clause (same as SELECT)
        $whereClauses = [];
        foreach ($this->wheres as $where) {
            $whereClauses[] = $where['column'] . ' ' . $where['operator'] . ' ' . $where['value'];
        }

        // Complete SQL: UPDATE posts SET title = :param1 WHERE id = :param2
        $sql = 'UPDATE ' . $this->table .
            ' SET ' . implode(', ', $setClauses) .
            ' WHERE ' . implode(' AND ', $whereClauses);

        // Prepeare and execute
        $stmt = $this->pdo->prepare($sql);

        foreach ($this->bindings as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();

        // Return number of affected rows
        return $stmt->rowCount();
    }

    /**
     * Delete records
     * 
     * Usage:
     * $db->table('posts')
     *   ->where('id', 5)
     *   ->delete();
     * 
     * IMPORTANT: Always use where() before delete()!
     * Otherwise you'll delete ALL rows (we'll add a safety check).
     * 
     * @return int Number of deleted rows
     */
    public function delete(): int
    {

        // Safety check: Prevent accidental deletion of all rows
        if (empty($this->wheres)) {
            throw new \Exception('Cannot delete without WHERE clause. Use deleteAll() if you really want to delete all rows.');
        }

        // Build WHERE clause
        $whereClauses = [];
        foreach ($this->wheres as $where) {
            $whereClauses[] = $where['column'] . ' ' . $where['operator'] . ' ' . $where['value'];
        }

        // Complete SQL: DELETE FROM posts WHERE id = :param1
        $sql = 'DELETE FROM ' . $this->table .
            ' WHERE ' . implode(' AND ', $whereClauses);

        // Prepare and execute
        $stmt = $this->pdo->prepare($sql);

        foreach ($this->bindings as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();

        // Return number of deleted rows
        return $stmt->rowCount();
    }

    /**
     * Update all rows (dangerous - use with caution!)
     * 
     * Only use this when you genuinely need to update every row in the table
     * 
     * Usage:
     * $db->table('posts')->updateAll(['status' => 'archived']);
     * 
     * @param array $data
     * @return int Number of affected rows
     */
    public function updateAll(array $data): int
    {
        $setClauses = [];
        foreach ($data as $column => $value) {
            $this->paramCounter++;
            $paramName = ':param' . $this->paramCounter;
            $setClauses[] = $column . ' = ' . $paramName;
            $this->bindings[$paramName] = $value;
        }

        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $setClauses);

        $stmt = $this->pdo->prepare($sql);

        foreach ($this->bindings as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Delete all rows (dangerous - use with caution!)
     * 
     * Only use this when you genuinely need to empty a table
     * 
     * Usage:
     * $db->table('logs')->deleteAll();
     * 
     * @return int Number of deleted rows
     */
    public function deleteAll()
    {
        $sql = 'DELETE FROM ' . $this->table;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
