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

        
        $xpSource = \Modules\Gamification\Models\XpSource::where('code', 'lesson_completed')
            ->active()
            ->first();
        $data['xp_reward'] = $xpSource ? $xpSource->xp_amount : 50;

        
        if ($isEnrolledStudent) {
            $progressInfo = $this->getStudentProgressInfo($user);
            $data['is_completed'] = $progressInfo['is_completed'];
            $data['is_locked'] = $progressInfo['is_locked'];
        }

        if ($isManager || $isEnrolledStudent) {
            $data['blocks'] = LessonBlockResource::collection($this->whenLoaded('blocks'));
        }

        
        if ($this->relationLoaded('unit')) {
            $unit = $this->unit;
            $data['unit'] = [
                'id' => $unit->id,
                'slug' => $unit->slug,
                'title' => $unit->title,
                'code' => $unit->code,
                'course_slug' => $unit->course_slug,
            ];

            
            if ($unit->relationLoaded('course')) {
                $course = $unit->course;
                $data['unit']['course'] = [
                    'id' => $course->id,
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'code' => $course->code,
                ];
            }

            
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
            return true; 
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

    private function getStudentProgressInfo(?object $user): array
    {
        if (! $user) {
            return ['is_completed' => false, 'is_locked' => false];
        }

        $unit = $this->relationLoaded('unit') ? $this->unit : $this->unit()->first();
        $course = $unit?->relationLoaded('course') ? $unit->course : $unit?->course()->first();

        if (! $course) {
            return ['is_completed' => false, 'is_locked' => false];
        }

        
        $enrollment = \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [\Modules\Enrollments\Enums\EnrollmentStatus::Active, \Modules\Enrollments\Enums\EnrollmentStatus::Completed])
            ->first();

        if (! $enrollment) {
            return ['is_completed' => false, 'is_locked' => true];
        }

        
        $lessonProgress = \Modules\Enrollments\Models\LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $this->id)
            ->first();

        $isCompleted = $lessonProgress && $lessonProgress->status === \Modules\Enrollments\Enums\ProgressStatus::Completed;

        
        $previousLessons = \Modules\Schemes\Models\Lesson::where('unit_id', $this->unit_id)
            ->where('order', '<', $this->order)
            ->where('status', 'published')
            ->pluck('id');

        $isLocked = false;
        if ($previousLessons->isNotEmpty()) {
            $completedCount = \Modules\Enrollments\Models\LessonProgress::where('enrollment_id', $enrollment->id)
                ->whereIn('lesson_id', $previousLessons)
                ->where('status', \Modules\Enrollments\Enums\ProgressStatus::Completed)
                ->count();

            $isLocked = $completedCount < $previousLessons->count();
        }

        return [
            'is_completed' => $isCompleted,
            'is_locked' => $isLocked,
        ];
    }
}
