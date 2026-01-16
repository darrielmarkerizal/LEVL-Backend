<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content_type' => $this->content_type,
            'content' => $this->content,
            'duration_minutes' => $this->duration_minutes,
            'order' => $this->order,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'blocks' => LessonBlockResource::collection($this->whenLoaded('blocks')),
        ];
    }
}
