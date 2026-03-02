<?php

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = auth('api')->user();
        $enrollment = null;

        if ($user && $user->hasRole('Student')) {
            $enrollment = $this->enrollments->where('user_id', $user->id)->first();
        }

        return [
            'id' => $this->id,
            'code' => $this->code,
            'slug' => $this->slug,
            'title' => $this->title,
            'short_desc' => $this->short_desc,
            'type' => $this->type,
            'level_tag' => $this->level_tag,
            'enrollment_type' => $this->enrollment_type,
            'status' => $this->status,
            'enrollment_status' => $user && $user->hasRole('Student') ? $enrollment?->status?->value : null,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'thumbnail' => $this->getFirstMediaUrl('thumbnail'),
            'banner' => $this->getFirstMediaUrl('banner'),
            'category' => $this->whenLoaded('category'),
            'instructor' => $this->instructor ? new \Modules\Auth\Http\Resources\UserResource($this->instructor) : null,
            'creator' => new \Modules\Auth\Http\Resources\UserResource($this->creator),
            'admins' => $this->whenLoaded('admins', fn () => \Modules\Auth\Http\Resources\UserResource::collection($this->admins)),
            'admins_count' => $this->admins_count ?? 0,
            'enrollments_count' => $this->enrollments_count ?? 0,
            'tags' => $this->whenLoaded('tags'),
            'units' => $this->whenLoaded('units'),
            'lessons' => $this->whenLoaded('lessons'),
            'quizzes' => $this->whenLoaded('quizzes'),
            'assignments' => $this->whenLoaded('assignments'),
            'enrollments' => $this->whenLoaded('enrollments'),
        ];
    }
}
