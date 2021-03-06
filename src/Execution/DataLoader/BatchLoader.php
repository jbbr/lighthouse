<?php

namespace Nuwave\Lighthouse\Execution\DataLoader;

use GraphQL\Deferred;
use Nuwave\Lighthouse\Support\Traits\HandlesCompositeKey;

abstract class BatchLoader
{
    use HandlesCompositeKey;

    /**
     * Keys to resolve.
     *
     * @var array
     */
    protected $keys = [];

    /**
     * Map of loaded results.
     *
     * [key => resolvedValue]
     *
     * @var mixed[]
     */
    private $results = [];

    /**
     * Check if data has been loaded.
     *
     * @var bool
     */
    private $hasLoaded = false;

    /**
     * Return an instance of a BatchLoader for a specific field.
     *
     * @param  string  $loaderClass     the class name of the concrete BatchLoader to instantiate
     * @param  mixed[] $pathToField     path to the GraphQL field from the root, is used as a key for BatchLoader instances
     * @param  mixed[] $constructorArgs those arguments are passed to the constructor of the new BatchLoader instance
     * @return static
     *
     * @throws \Exception
     */
    public static function instance(string $loaderClass, array $pathToField, array $constructorArgs = []): self
    {
        // The path to the field serves as the unique key for the instance
        $instanceName = static::instanceKey($pathToField);

        // If we are resolving a batched query, we need to assign each
        // query a uniquely indexed instance
        $currentBatchIndex = app('graphql')->currentBatchIndex();

        if ($currentBatchIndex !== null) {
            $instanceName = "batch_{$currentBatchIndex}_{$instanceName}";
        }

        // Only register a new instance if it is not already bound
        $instance = app()->bound($instanceName)
            ? app($instanceName)
            : app()->instance(
                $instanceName,
                app()->makeWith($loaderClass, $constructorArgs)
            );

        if (! $instance instanceof self) {
            throw new \Exception(
                "The given class '$loaderClass' must resolve to an instance of Nuwave\Lighthouse\Execution\DataLoader\BatchLoader"
            );
        }

        return $instance;
    }

    /**
     * Generate a unique key for the instance, using the path in the query.
     *
     * @param  mixed[] $path
     * @return string
     */
    public static function instanceKey(array $path): string
    {
        return collect($path)
            ->filter(function ($path) {
                // Ignore numeric path entries, as those signify an array of fields
                // Those are the very purpose for this batch loader, so they must not be included.
                return ! is_numeric($path);
            })
            ->implode('_');
    }

    /**
     * Load object by key.
     *
     * @param  mixed  $key
     * @param  array  $metaInfo
     * @return \GraphQL\Deferred
     */
    public function load($key, array $metaInfo = []): Deferred
    {
        $key = $this->buildKey($key);
        $this->keys[$key] = $metaInfo;

        return new Deferred(function () use ($key) {
            if (! $this->hasLoaded) {
                $this->results = $this->resolve();
                $this->hasLoaded = true;
            }

            return $this->results[$key];
        });
    }

    /**
     * Resolve the keys.
     *
     * The result has to be an associative array: [key => result]
     *
     * @return array
     */
    abstract public function resolve(): array;
}
