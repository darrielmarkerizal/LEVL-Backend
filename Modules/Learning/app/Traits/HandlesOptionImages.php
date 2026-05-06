<?php

declare(strict_types=1);

namespace Modules\Learning\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

trait HandlesOptionImages
{
    protected function processOptionImages(Model $question, array $options): void
    {
        foreach ($options as &$option) {
            if (is_array($option) && isset($option['image']) && $option['image'] instanceof UploadedFile) {
                $media = $question->addMedia($option['image'])->toMediaCollection('option_images');
                $option['image'] = $media->getUrl();
            }
        }
        unset($option);

        $question->options = $options;
        $question->save();
    }
}
