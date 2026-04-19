<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


abstract class BaseResource extends JsonResource
{
    
    protected array $defaultRelations = [];

    
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

    
    public static function collection($resource)
    {
        
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

    
    public function getDefaultRelations(): array
    {
        return $this->defaultRelations;
    }

    
    public function withDefaultRelations(array $relations): self
    {
        $this->defaultRelations = $relations;

        return $this;
    }

    
    public function addDefaultRelations(array $relations): self
    {
        $this->defaultRelations = array_unique(
            array_merge($this->defaultRelations, $relations)
        );

        return $this;
    }
}
