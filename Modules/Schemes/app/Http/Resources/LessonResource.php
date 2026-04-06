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

        // Add XP reward information
        $xpSource = \Modules\Gamification\Models\XpSource::where('code', 'lesson_completed')
            ->where('is_active', true)
            ->first();
        $data['xp_reward'] = $xpSource ? $xpSource->xp_amount : 50;

        if ($isManager || $isEnrolledStudent) {
            $data['blocks'] = LessonBlockResource::collection($this->whenLoaded('blocks'));
        }

        // Add unit and course info when included via Spatie Query Builder
        if ($this->relationLoaded('unit')) {
            $unit = $this->unit;
            $data['unit'] = [
                'id' => $unit->id,
                'slug' => $unit->slug,
                'title' => $unit->title,
                'code' => $unit->code,
                'course_slug' => $unit->course_slug,
            ];

            // Add course info if loaded
            if ($unit->relationLoaded('course')) {
                $course = $unit->course;
                $data['unit']['course'] = [
                    'id' => $course->id,
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'code' => $course->code,
                ];
            }

            // Add sequence (format: unit_order.lesson_order)
            $data['sequence'] = $unit->order . '.' . $this->order;
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
            return true; // Admins have global access to all courses
        }

        if ($user->hasRole('Instructor')) {
            return $course->instructors()->where('user_id', $user->id)->exists();
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
