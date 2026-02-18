<?php

declare(strict_types=1);

namespace Core;

/**
 * Model-aware Query Builder
 *
 * Wraps the base QueryBuilder and automatically converts
 * raw stdClass results into proper Model instances (hydration).
 *
 * This is what makes Post::where('status', 'published')->get()
 * return an array of Post objects instead of stdClass objects.
 */
class ModelQueryBuilder
{
    /**
     * The underlying query builder instance
     */
    protected $query;

    /**
     * The model class to hydrate results into
     */
    protected $modelClass;

    /**
     * @param QueryBuilder $query
     * @param string $modelClass The fully-qualified model class name
     */
    public function __construct(QueryBuilder $query, string $modelClass)
    {
        $this->query = $query;
        $this->modelClass = $modelClass;
    }

    /**
     * Execute the query and return hydrated model instances
     *
     * @return array Array of Model instances
     */
    public function get()
    {
        $results = $this->query->get();

        if (empty($results)) {
            return [];
        }

        return $this->hydrateResults($results);
    }

    /**
     * Execute the query and return the first hydrated model instance
     *
     * @return Model|null
     */
    public function first()
    {
        $result = $this->query->first();

        if (!$result) {
            return null;
        }

        return $this->hydrateOne($result);
    }

    /**
     * Find a record by ID and return a hydrated model
     *
     * @param mixed $id
     * @return Model|null
     */
    public function find($id)
    {
        $result = $this->query->find($id);

        if (!$result) {
            return null;
        }

        return $this->hydrateOne($result);
    }

    /**
     * Forward all other method calls to the underlying query builder
     * and return $this for continued chaining
     *
     * Methods like where(), orderBy(), limit(), select(), join(), etc.
     * all return the query builder for chaining — we intercept that
     * and return $this instead so hydration still happens at the end.
     *
     * Methods like count(), exists(), insert(), update(), delete()
     * return scalar values — we pass those through as-is.
     */
    public function __call($method, $parameters)
    {
        $result = call_user_func_array([$this->query, $method], $parameters);

        // If the query builder returned itself (chaining), return $this instead
        if ($result === $this->query) {
            return $this;
        }

        // Otherwise return the raw result (count, exists, insert, update, delete)
        return $result;
    }

    /**
     * Convert an array of stdClass results into model instances
     */
    protected function hydrateResults(array $results): array
    {
        $models = [];

        foreach ($results as $result) {
            $models[] = $this->hydrateOne($result);
        }

        return $models;
    }

    /**
     * Convert a single stdClass result into a model instance
     *
     * Delegates to the model's own newFromRow() method
     */
    protected function hydrateOne($result)
    {
        $class = $this->modelClass;
        return $class::newFromRow($result);
    }
}
