<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth('api')->user();
        $isManager = $this->isManager($user);
        $isEnrolledStudent = $this->isEnrolledStudent($user);

        $data = [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'order' => $this->order,
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];

        if ($isManager || $isEnrolledStudent) {
            $data['blocks'] = LessonBlockResource::collection($this->whenLoaded('blocks'));
        }

        return $data;
    }

    private function isManager(?object $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Superadmin')) {
            return true;
        }

        $unit = $this->relationLoaded('unit') ? $this->unit : $this->unit()->first();
        $course = $unit?->relationLoaded('course') ? $unit->course : $unit?->course()->first();

        if (! $course) {
            return false;
        }

        if ($user->hasRole('Admin')) {
            return $course->admins()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('Instructor')) {
            return $course->instructor_id === $user->id;
        }

        return false;
    }

    private function isEnrolledStudent(?object $user): bool
    {
        if (! $user || ! $user->hasRole('Student')) {
            return false;
        }

        $unit = $this->relationLoaded('unit') ? $this->unit : $this->unit()->first();
        $course = $unit?->relationLoaded('course') ? $unit->course : $unit?->course()->first();

        if (! $course) {
            return false;
        }

        return $course->enrollments()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }
}
