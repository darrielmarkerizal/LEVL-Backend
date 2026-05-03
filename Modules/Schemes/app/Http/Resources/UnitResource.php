<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Enrollments\Enums\ProgressStatus;
use Modules\Schemes\Models\UnitContent;

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
            if ($this->relationLoaded('lessons') && $this->relationLoaded('quizzes') && $this->relationLoaded('assignments')) {
                $unitContentsMap = UnitContent::where('unit_id', $this->id)
                    ->get()
                    ->mapWithKeys(fn ($uc) => [$uc->contentable_type.'_'.$uc->contentable_id => $uc->order]);

                $getOrder = fn (string $type, int $id): int => $unitContentsMap->get($type.'_'.$id, PHP_INT_MAX);

                $elements = collect();
                $unitOrder = $this->order;

                foreach ($this->lessons as $lesson) {
                    $order = $getOrder('lesson', $lesson->id);
                    $elements->push([
                        'id' => $lesson->id,
                        'type' => 'lesson',
                        'title' => $lesson->title,
                        'slug' => $lesson->slug,
                        'order' => $order,
                        'sequence' => $unitOrder.'.'.$order,
                        'status' => $lesson->status,
                        'duration_minutes' => $lesson->duration_minutes,
                        'xp_reward' => $lesson->xp_reward,
                        'created_at' => $lesson->created_at?->toIso8601String(),
                        'updated_at' => $lesson->updated_at?->toIso8601String(),
                    ]);
                }

                foreach ($this->quizzes as $quiz) {
                    $order = $getOrder('quiz', $quiz->id);
                    $elements->push([
                        'id' => $quiz->id,
                        'type' => 'quiz',
                        'title' => $quiz->title,
                        'order' => $order,
                        'sequence' => $unitOrder.'.'.$order,
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
                    $order = $getOrder('assignment', $assignment->id);
                    $elements->push([
                        'id' => $assignment->id,
                        'type' => 'assignment',
                        'title' => $assignment->title,
                        'order' => $order,
                        'sequence' => $unitOrder.'.'.$order,
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

        $summary = $this->getUnitCompletionSummary($this->id, (int) $enrollment->id, (int) $enrollment->user_id);
        $totalContent = $summary['total_items'];

        if ($totalContent === 0) {
            return [
                'percentage' => 0,
                'completed_items' => 0,
                'total_items' => $totalContent,
                'status' => ProgressStatus::NotStarted->value,
                'is_locked' => $this->isUnitLocked($enrollment),
            ];
        }

        $completedItems = $summary['completed_items'];
        $percentage = round(($completedItems / $totalContent) * 100, 2);
        $status = $this->resolveProgressStatus($completedItems, $totalContent, $unitProgress?->status);

        return [
            'percentage' => $percentage,
            'completed_items' => $completedItems,
            'total_items' => $totalContent,
            'status' => $status,
            'is_locked' => $this->isUnitLocked($enrollment),
        ];
    }

    private function resolveProgressStatus(int $completedItems, int $totalItems, mixed $persistedStatus): string
    {
        if ($totalItems <= 0 || $completedItems <= 0) {
            return ProgressStatus::NotStarted->value;
        }

        
        
        
        
        
        if ($completedItems >= $totalItems) {
            
            if ($persistedStatus instanceof ProgressStatus && $persistedStatus === ProgressStatus::Completed) {
                return ProgressStatus::Completed->value;
            }
            if (is_string($persistedStatus) && $persistedStatus === ProgressStatus::Completed->value) {
                return ProgressStatus::Completed->value;
            }
            
            return ProgressStatus::InProgress->value;
        }

        return ProgressStatus::InProgress->value;
    }

    private function isUnitLocked($enrollment): bool
    {
        $prerequisiteService = app(\Modules\Schemes\Services\PrerequisiteService::class);
        $accessCheck = $prerequisiteService->checkUnitAccess($this->resource, (int) $enrollment->user_id);

        return ! ($accessCheck['accessible'] ?? true);
    }

    private function getUnitCompletionSummary(int $unitId, int $enrollmentId, int $userId): array
    {
        $prerequisiteService = app(\Modules\Schemes\Services\PrerequisiteService::class);

        return $prerequisiteService->getUnitCompletionCounts($unitId, $enrollmentId, $userId);
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
