<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth('api')->user();
        $isManager = $this->isManager($user);
        $isEnrolledStudent = $this->isEnrolledStudent($user);

        $data = [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'code' => $this->code,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'order' => $this->order,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];

        if ($isManager || $isEnrolledStudent) {
            $data['lessons'] = LessonResource::collection($this->whenLoaded('lessons'));
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

        $course = $this->relationLoaded('course') ? $this->course : $this->course()->first();

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

        $course = $this->relationLoaded('course') ? $this->course : $this->course()->first();

        if (! $course) {
            return false;
        }

        return $course->enrollments()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }
}
