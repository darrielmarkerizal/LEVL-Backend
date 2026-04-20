<?php

declare(strict_types=1);

namespace App\Support\MediaLibrary;

use RuntimeException;
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
        $modelType = $this->getModelTypeName($this->toString($media->getAttributeValue('model_type')));
        $modelId = $this->toString($media->getAttributeValue('model_id'));
        $collection = $this->toString($media->getAttributeValue('collection_name'));
        $uuidValue = $media->getAttributeValue('uuid');
        $idValue = $media->getAttributeValue('id');
        $uuid = $uuidValue !== null && $uuidValue !== '' ? $this->toString($uuidValue) : $this->toString($idValue);

        return "{$modelType}/{$modelId}/{$collection}/{$uuid}";
    }

    protected function getModelTypeName(string $modelClass): string
    {
        $className = class_basename($modelClass);
        if (! is_string($className)) {
            throw new RuntimeException('Invalid model class basename.');
        }

        $normalized = preg_replace('/(?<!^)[A-Z]/', '_$0', $className);
        if (! is_string($normalized)) {
            throw new RuntimeException('Failed to normalize model class name.');
        }

        return strtolower($normalized).'s';
    }

    private function toString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return (string) $value;
        }

        if ($value === null) {
            return '';
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        throw new RuntimeException('Unable to convert media attribute to string.');
    }
}
