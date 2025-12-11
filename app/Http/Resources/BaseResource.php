<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base Resource class with consistent relationship loading.
 *
 * All API resources should extend this base class to ensure
 * consistent behavior and automatic relationship loading.
 *
 * Usage:
 * ```php
 * class UserResource extends BaseResource
 * {
 *     protected array $defaultRelations = ['roles', 'profile'];
 *
 *     public function toArray(Request $request): array
 *     {
 *         return [
 *             'id' => $this->id,
 *             'name' => $this->name,
 *         ];
 *     }
 * }
 * ```
 */
abstract class BaseResource extends JsonResource
{
    /**
     * Default relationships to load when creating the resource.
     *
     * Override this in child classes to specify which relationships
     * should be automatically loaded.
     *
     * @var array<int, string>
     */
    protected array $defaultRelations = [];

    /**
     * Create a new resource instance with automatic relationship loading.
     *
     * @param  mixed  ...$parameters
     * @return static
     */
    public static function make(...$parameters)
    {
        $resource = $parameters[0] ?? null;

        if ($resource && method_exists($resource, 'loadMissing')) {
            $instance = new static($resource);
            
            if (! empty($instance->defaultRelations)) {
                $resource->loadMissing($instance->defaultRelations);
            }
        }

        return parent::make(...$parameters);
    }

    /**
     * Create a new resource collection with automatic relationship loading.
     *
     * @param  mixed  $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function collection($resource)
    {
        // Load relationships for each item in the collection
        if ($resource instanceof \Illuminate\Support\Collection || is_array($resource)) {
            $instance = new static(null);
            
            if (! empty($instance->defaultRelations)) {
                foreach ($resource as $item) {
                    if (method_exists($item, 'loadMissing')) {
                        $item->loadMissing($instance->defaultRelations);
                    }
                }
            }
        }

        return parent::collection($resource);
    }

    /**
     * Get the default relations for this resource.
     *
     * @return array<int, string>
     */
    public function getDefaultRelations(): array
    {
        return $this->defaultRelations;
    }

    /**
     * Set default relations dynamically.
     *
     * @param  array<int, string>  $relations
     * @return $this
     */
    public function withDefaultRelations(array $relations): self
    {
        $this->defaultRelations = $relations;

        return $this;
    }

    /**
     * Add additional relations to the default set.
     *
     * @param  array<int, string>  $relations
     * @return $this
     */
    public function addDefaultRelations(array $relations): self
    {
        $this->defaultRelations = array_unique(
            array_merge($this->defaultRelations, $relations)
        );

        return $this;
    }
}
