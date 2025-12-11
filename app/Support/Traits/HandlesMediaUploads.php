<?php

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

trait HandlesMediaUploads
{
    protected function uploadMedia(Model $model, Request $request, array $collections): void
    {
        foreach ($collections as $fieldName => $collection) {
            if ($request->hasFile($fieldName)) {
                $this->uploadSingleMedia($model, $request->file($fieldName), $collection);
            }
        }
    }

    protected function uploadSingleMedia(
        Model $model,
        UploadedFile $file,
        string $collection,
        bool $clearExisting = true
    ) {
        if ($clearExisting) {
            $model->clearMediaCollection($collection);
        }

        return $model->addMedia($file)->toMediaCollection($collection);
    }

    protected function uploadMultipleMedia(Model $model, array $files, string $collection): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $model->addMedia($file)->toMediaCollection($collection);
            }
        }
    }

    protected function replaceMedia(Model $model, UploadedFile $file, string $collection)
    {
        return $this->uploadSingleMedia($model, $file, $collection, true);
    }

    protected function uploadMediaWithProperties(
        Model $model,
        UploadedFile $file,
        string $collection,
        array $customProperties = []
    ) {
        return $model->addMedia($file)
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection);
    }
}
