<?php

namespace App\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;


class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive/';
    }

    protected function getBasePath(Media $media): string
    {
        
        $modelType = $this->getModelTypeName($media->model_type);

        
        $modelId = $media->model_id;

        
        $collection = $media->collection_name;

        
        $uuid = $media->uuid ?: $media->id;

        return "{$modelType}/{$modelId}/{$collection}/{$uuid}";
    }

    protected function getModelTypeName(string $modelClass): string
    {
        
        
        $className = class_basename($modelClass);

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)).'s';
    }
}
