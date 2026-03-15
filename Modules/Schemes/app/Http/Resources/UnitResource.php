<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    protected $enrollment;

    public function __construct($resource, $enrollment = null)
    {
        parent::__construct($resource);
        $this->enrollment = $enrollment;
    }

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

        // Add progress info for students
        if ($isEnrolledStudent && $this->enrollment) {
            $data['progress'] = $this->getUnitProgress($this->enrollment);
        }

        return $data;
    }

    private function getUnitProgress($enrollment): array
    {
        // Get unit progress
        $unitProgress = \Modules\Enrollments\Models\UnitProgress::where('enrollment_id', $enrollment->id)
            ->where('unit_id', $this->id)
            ->first();

        // Count total content items (lessons + quizzes + assignments)
        $totalLessons = $this->lessons()->where('status', 'published')->count();
        $totalQuizzes = \Modules\Learning\Models\Quiz::where('unit_id', $this->id)
            ->where('status', \Modules\Learning\Enums\QuizStatus::Published)
            ->count();
        $totalAssignments = \Modules\Learning\Models\Assignment::where('unit_id', $this->id)
            ->where('status', \Modules\Learning\Enums\AssignmentStatus::Published)
            ->count();
        
        $totalContent = $totalLessons + $totalQuizzes + $totalAssignments;

        if (!$unitProgress || $totalContent === 0) {
            return [
                'percentage' => 0,
                'completed_items' => 0,
                'total_items' => $totalContent,
                'status' => 'not_started',
                'is_locked' => $this->isUnitLocked($enrollment),
            ];
        }

        // Count completed items
        $lessonIds = $this->lessons()->where('status', 'published')->pluck('id');
        $completedLessons = \Modules\Enrollments\Models\LessonProgress::where('enrollment_id', $enrollment->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('status', \Modules\Enrollments\Enums\ProgressStatus::Completed)
            ->count();

        $quizIds = \Modules\Learning\Models\Quiz::where('unit_id', $this->id)
            ->where('status', \Modules\Learning\Enums\QuizStatus::Published)
            ->pluck('id');
        $completedQuizzes = \Modules\Learning\Models\QuizSubmission::where('user_id', $enrollment->user_id)
            ->whereIn('quiz_id', $quizIds)
            ->whereHas('quiz', function($q) {
                $q->whereRaw('quiz_submissions.score >= quizzes.passing_grade');
            })
            ->distinct('quiz_id')
            ->count('quiz_id');

        $assignmentIds = \Modules\Learning\Models\Assignment::where('unit_id', $this->id)
            ->where('status', \Modules\Learning\Enums\AssignmentStatus::Published)
            ->pluck('id');
        $completedAssignments = \Modules\Learning\Models\Submission::where('user_id', $enrollment->user_id)
            ->whereIn('assignment_id', $assignmentIds)
            ->where('status', \Modules\Learning\Enums\SubmissionStatus::Graded)
            ->whereHas('assignment', function($q) {
                $q->whereRaw('submissions.score >= (assignments.max_score * 0.6)');
            })
            ->distinct('assignment_id')
            ->count('assignment_id');

        $completedItems = $completedLessons + $completedQuizzes + $completedAssignments;
        $percentage = $totalContent > 0 ? round(($completedItems / $totalContent) * 100, 2) : 0;

        return [
            'percentage' => $percentage,
            'completed_items' => $completedItems,
            'total_items' => $totalContent,
            'status' => $unitProgress->status->value,
            'is_locked' => $this->isUnitLocked($enrollment),
        ];
    }

    private function isUnitLocked($enrollment): bool
    {
        // First unit is never locked
        if ($this->order === 1) {
            return false;
        }

        // Get the course to access all units
        $course = $this->relationLoaded('course') ? $this->course : $this->course()->first();
        
        if (!$course) {
            return false;
        }

        // Get previous unit (order - 1)
        $previousUnit = \Modules\Schemes\Models\Unit::where('course_id', $course->id)
            ->where('order', $this->order - 1)
            ->first();

        if (!$previousUnit) {
            return false;
        }

        // Check if previous unit is completed
        $previousUnitProgress = \Modules\Enrollments\Models\UnitProgress::where('enrollment_id', $enrollment->id)
            ->where('unit_id', $previousUnit->id)
            ->first();

        // Unit is locked if previous unit is not completed
        if (!$previousUnitProgress) {
            return true;
        }

        return $previousUnitProgress->status->value !== 'completed';
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
