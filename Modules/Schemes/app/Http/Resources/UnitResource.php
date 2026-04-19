<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    protected $enrollment;
    protected ?array $elements = null;

    public function __construct($resource, $enrollment = null)
    {
        parent::__construct($resource);
        $this->enrollment = $enrollment;
    }

    public function setElements(array $elements): static
    {
        $this->elements = $elements;
        return $this;
    }

    public function toArray(Request $request): array
    {
        $user = auth('api')->user();
        $isManager = $this->isManager($user);
        $isEnrolledStudent = $this->isEnrolledStudent($user);

        $data = [
            'id' => $this->id,
            'course_slug' => $this->course?->slug,
            'course_name' => $this->course?->title,
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
            
            
            $data['quizzes'] = $this->whenLoaded('quizzes', function () {
                return $this->quizzes->map(function ($quiz) {
                    return [
                        'id' => $quiz->id,
                        'title' => $quiz->title,
                        'order' => $quiz->order,
                        'status' => $quiz->status?->value,
                        'status_label' => $quiz->status ? __('enums.quiz_status.'.$quiz->status->value) : null,
                        'randomization_type' => $quiz->randomization_type,
                        'time_limit_minutes' => $quiz->time_limit_minutes,
                        'passing_grade' => $quiz->passing_grade,
                        'max_attempts' => $quiz->max_attempts,
                        'max_score' => $quiz->max_score,
                        'created_at' => $quiz->created_at?->toIso8601String(),
                        'updated_at' => $quiz->updated_at?->toIso8601String(),
                    ];
                });
            });
            
            $data['assignments'] = $this->whenLoaded('assignments', function () {
                return $this->assignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->id,
                        'title' => $assignment->title,
                        'order' => $assignment->order,
                        'status' => $assignment->status?->value,
                        'status_label' => $assignment->status ? __('enums.assignment_status.'.$assignment->status->value) : null,
                        'submission_type' => $assignment->submission_type?->value,
                        'submission_type_label' => $assignment->submission_type ? __('enums.submission_type.'.$assignment->submission_type->value) : null,
                        'max_score' => $assignment->max_score,
                        'created_at' => $assignment->created_at?->toIso8601String(),
                        'updated_at' => $assignment->updated_at?->toIso8601String(),
                    ];
                });
            });
            
            
            if ($this->relationLoaded('lessons') && $this->relationLoaded('quizzes') && $this->relationLoaded('assignments')) {
                $elements = collect();
                $unitOrder = $this->order;
                
                
                foreach ($this->lessons as $lesson) {
                    $elements->push([
                        'id' => $lesson->id,
                        'type' => 'lesson',
                        'title' => $lesson->title,
                        'slug' => $lesson->slug,
                        'order' => $lesson->order,
                        'sequence' => $unitOrder . '.' . $lesson->order,
                        'status' => $lesson->status,
                        'duration_minutes' => $lesson->duration_minutes,
                        'xp_reward' => $lesson->xp_reward,
                        'created_at' => $lesson->created_at?->toIso8601String(),
                        'updated_at' => $lesson->updated_at?->toIso8601String(),
                    ]);
                }
                
                
                foreach ($this->quizzes as $quiz) {
                    $elements->push([
                        'id' => $quiz->id,
                        'type' => 'quiz',
                        'title' => $quiz->title,
                        'order' => $quiz->order,
                        'sequence' => $unitOrder . '.' . $quiz->order,
                        'status' => $quiz->status?->value,
                        'status_label' => $quiz->status ? __('enums.quiz_status.'.$quiz->status->value) : null,
                        'randomization_type' => $quiz->randomization_type,
                        'time_limit_minutes' => $quiz->time_limit_minutes,
                        'passing_grade' => $quiz->passing_grade,
                        'max_attempts' => $quiz->max_attempts,
                        'max_score' => $quiz->max_score,
                        'created_at' => $quiz->created_at?->toIso8601String(),
                        'updated_at' => $quiz->updated_at?->toIso8601String(),
                    ]);
                }
                
                
                foreach ($this->assignments as $assignment) {
                    $elements->push([
                        'id' => $assignment->id,
                        'type' => 'assignment',
                        'title' => $assignment->title,
                        'order' => $assignment->order,
                        'sequence' => $unitOrder . '.' . $assignment->order,
                        'status' => $assignment->status?->value,
                        'status_label' => $assignment->status ? __('enums.assignment_status.'.$assignment->status->value) : null,
                        'submission_type' => $assignment->submission_type?->value,
                        'submission_type_label' => $assignment->submission_type ? __('enums.submission_type.'.$assignment->submission_type->value) : null,
                        'max_score' => $assignment->max_score,
                        'created_at' => $assignment->created_at?->toIso8601String(),
                        'updated_at' => $assignment->updated_at?->toIso8601String(),
                    ]);
                }
                
                
                $data['elements'] = $elements->sortBy('order')->values()->all();
            }
        }

        
        if ($isEnrolledStudent && $this->enrollment) {
            $data['progress'] = $this->getUnitProgress($this->enrollment);
        }

        if ($this->elements !== null) {
            $data['elements'] = $this->elements;
        }

        return $data;
    }

    private function getUnitProgress($enrollment): array
    {
        
        $unitProgress = \Modules\Enrollments\Models\UnitProgress::where('enrollment_id', $enrollment->id)
            ->where('unit_id', $this->id)
            ->first();

        
        $totalLessons = $this->lessons()->where('status', 'published')->count();
        $totalQuizzes = \Modules\Learning\Models\Quiz::where('unit_id', $this->id)
            ->where('status', \Modules\Learning\Enums\QuizStatus::Published)
            ->count();
        $totalAssignments = \Modules\Learning\Models\Assignment::where('unit_id', $this->id)
            ->where('status', \Modules\Learning\Enums\AssignmentStatus::Published)
            ->count();

        $totalContent = $totalLessons + $totalQuizzes + $totalAssignments;

        if (! $unitProgress || $totalContent === 0) {
            return [
                'percentage' => 0,
                'completed_items' => 0,
                'total_items' => $totalContent,
                'status' => 'not_started',
                'is_locked' => $this->isUnitLocked($enrollment),
            ];
        }

        
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
            ->whereHas('quiz', function ($q) {
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
            ->whereHas('assignment', function ($q) {
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
        
        if ($this->order === 1) {
            return false;
        }

        
        $course = $this->relationLoaded('course') ? $this->course : $this->course()->first();

        if (! $course) {
            return false;
        }

        
        $previousUnit = \Modules\Schemes\Models\Unit::where('course_id', $course->id)
            ->where('order', $this->order - 1)
            ->first();

        if (! $previousUnit) {
            return false;
        }

        
        $previousUnitProgress = \Modules\Enrollments\Models\UnitProgress::where('enrollment_id', $enrollment->id)
            ->where('unit_id', $previousUnit->id)
            ->first();

        
        if (! $previousUnitProgress) {
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

        $course = $this->relationLoaded('course') ? $this->course : $this->course()->first();

        if (! $course) {
            return false;
        }

        return $course->enrollments()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->exists();
    }
}
