<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'slug' => $this->slug,
            'title' => $this->title,
            'short_desc' => $this->short_desc,
            'type' => $this->type,
            'level_tag' => $this->level_tag,
            'category_id' => $this->category_id,
            'tags_json' => $this->tags_json,
            'prereq_text' => $this->prereq_text,
            'duration_estimate' => $this->duration_estimate,
            'progression_mode' => $this->progression_mode,
            'enrollment_type' => $this->enrollment_type,
            'enrollment_key' => $this->enrollment_key, // Typically we might obscure this, but keeping it for now
            'status' => $this->status,
            'published_at' => $this->published_at,
            'instructor_id' => $this->instructor_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tags' => $this->whenLoaded('tags'),
            'outcomes' => $this->whenLoaded('outcomes'),
            'thumbnail_url' => $this->getFirstMediaUrl('thumbnail'),
            'banner_url' => $this->getFirstMediaUrl('banner'),
        ];
    }
}
