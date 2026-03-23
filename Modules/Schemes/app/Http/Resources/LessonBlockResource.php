<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'slug' => $this->slug,
            'block_type' => $this->block_type->value, // Serialize enum to string value
            'content' => $this->content,
            'order' => $this->order,
            
            // External URL fields
            'external_url' => $this->external_url,
            'embed_url' => $this->embed_url,
            
            // Media fields (for uploaded files)
            'media' => (function () {
                $media = $this->getFirstMedia('media');

                return $media ? [
                    'url' => $media->getUrl(),
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ] : null;
            })(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
