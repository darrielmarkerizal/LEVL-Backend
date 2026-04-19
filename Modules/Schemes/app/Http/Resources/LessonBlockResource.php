<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonBlockResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'slug' => $this->slug,
            'block_type' => $this->block_type->value,
            'content' => $this->content,
            'order' => $this->order,
            
            'external_url' => $this->external_url,
            'embed_url' => $this->embed_url,
            
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
            
            'lesson' => $this->whenLoaded('lesson', function () {
                $lesson = $this->lesson;
                return [
                    'id' => $lesson->id,
                    'unit_id' => $lesson->unit_id,
                    'slug' => $lesson->slug,
                    'title' => $lesson->title,
                    'description' => $lesson->description,
                    'order' => $lesson->order,
                    'duration_minutes' => $lesson->duration_minutes,
                    'status' => $lesson->status->value,
                    'created_at' => $lesson->created_at?->toIso8601String(),
                    'updated_at' => $lesson->updated_at?->toIso8601String(),
                    'xp_reward' => $lesson->xp_reward,
                    'unit' => $lesson->relationLoaded('unit') ? [
                        'id' => $lesson->unit->id,
                        'slug' => $lesson->unit->slug,
                        'title' => $lesson->unit->title,
                        'code' => $lesson->unit->code,
                        'course_slug' => $lesson->unit->course_slug,
                        'course' => $lesson->unit->relationLoaded('course') ? [
                            'id' => $lesson->unit->course->id,
                            'slug' => $lesson->unit->course->slug,
                            'title' => $lesson->unit->course->title,
                            'code' => $lesson->unit->course->code,
                        ] : null,
                    ] : null,
                ];
            }),
        ];
    }
}
