<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Models\UnitContent;

class PrerequisiteService
{
    public function checkLessonAccess(Lesson $lesson, int $userId): array
    {
        $uc = UnitContent::where('contentable_type', 'lesson')
            ->where('contentable_id', $lesson->id)
            ->first();

        if (! $uc) {
            return ['accessible' => true, 'missing' => []];
        }

        $allContent = $this->loadUnitContentsBefore($uc->unit_id, $uc->order);
        $missing = $this->checkContentCompletion($allContent, $userId);

        return [
            'accessible' => empty($missing),
            'missing' => $missing,
        ];
    }

    public function checkAssignmentAccess(Assignment $assignment, int $userId): array
    {
        $unitId = $assignment->unit_id;

        if (! $unitId) {
            return ['accessible' => true, 'missing' => []];
        }

        $unit = Unit::find($unitId);
        if (! $unit) {
            return ['accessible' => true, 'missing' => []];
        }

        $uc = UnitContent::where('contentable_type', 'assignment')
            ->where('contentable_id', $assignment->id)
            ->first();

        if (! $uc) {
            return ['accessible' => true, 'missing' => []];
        }

        $missing = [];

        if ($unit->order > 1) {
            $previousUnits = Unit::where('course_id', $unit->course_id)
                ->where('order', '<', $unit->order)
                ->orderBy('order')
                ->get();

            foreach ($previousUnits as $prevUnit) {
                $unitMissing = $this->getUnitIncompleteness($prevUnit, $userId);
                $missing = array_merge($missing, $unitMissing);
            }
        }

        $allContent = $this->loadUnitContentsBefore($unitId, $uc->order);
        $currentUnitMissing = $this->checkContentCompletion($allContent, $userId, $unit);
        $missing = array_merge($missing, $currentUnitMissing);

        return [
            'accessible' => empty($missing),
            'missing' => $missing,
        ];
    }

    public function checkQuizAccess(Quiz $quiz, int $userId): array
    {
        $unitId = $quiz->unit_id;

        if (! $unitId) {
            return ['accessible' => true, 'missing' => []];
        }

        $unit = Unit::find($unitId);
        if (! $unit) {
            return ['accessible' => true, 'missing' => []];
        }

        $uc = UnitContent::where('contentable_type', 'quiz')
            ->where('contentable_id', $quiz->id)
            ->first();

        if (! $uc) {
            return ['accessible' => true, 'missing' => []];
        }

        $missing = [];

        if ($unit->order > 1) {
            $previousUnits = Unit::where('course_id', $unit->course_id)
                ->where('order', '<', $unit->order)
                ->orderBy('order')
                ->get();

            foreach ($previousUnits as $prevUnit) {
                $unitMissing = $this->getUnitIncompleteness($prevUnit, $userId);
                $missing = array_merge($missing, $unitMissing);
            }
        }

        $allContent = $this->loadUnitContentsBefore($unitId, $uc->order);
        $currentUnitMissing = $this->checkContentCompletion($allContent, $userId, $unit);
        $missing = array_merge($missing, $currentUnitMissing);

        return [
            'accessible' => empty($missing),
            'missing' => $missing,
        ];
    }

    public function checkUnitAccess(Unit $unit, int $userId): array
    {
        if ($unit->order === 1) {
            return ['accessible' => true, 'missing' => []];
        }

        $previousUnit = Unit::where('course_id', $unit->course_id)
            ->where('order', '<', $unit->order)
            ->orderBy('order', 'desc')
            ->first();

        if (! $previousUnit) {
            return ['accessible' => true, 'missing' => []];
        }

        $missing = $this->getUnitIncompleteness($previousUnit, $userId);

        if (! empty($missing)) {
            return [
                'accessible' => false,
                'missing' => $missing,
                'message' => 'Complete all lessons and pass all assignments/quizzes in previous unit',
            ];
        }

        return ['accessible' => true, 'missing' => []];
    }

    private function getUnitIncompleteness(Unit $unit, int $userId): array
    {
        $missing = [];
        $unitContents = $this->loadAllUnitContents($unit->id);

        foreach ($unitContents as $uc) {
            $model = $uc->contentable;
            $type = $uc->contentable_type;

            $isPublished = match ($type) {
                'lesson' => ($model->status?->value ?? $model->status) === 'published',
                'assignment' => $model->status === \Modules\Learning\Enums\AssignmentStatus::Published,
                'quiz' => $model->status === \Modules\Learning\Enums\QuizStatus::Published,
                default => false,
            };

            if (! $isPublished) {
                continue;
            }

            $isCompleted = match ($type) {
                'lesson' => $model->isCompletedBy($userId),
                'assignment' => $this->isAssignmentPassed($model, $userId),
                'quiz' => $this->isQuizPassed($model, $userId),
                default => false,
            };

            if (! $isCompleted) {
                $entry = [
                    'type' => $type,
                    'id' => $model->id,
                    'title' => $model->title,
                    'slug' => $model->slug ?? null,
                    'unit_title' => $unit->title,
                ];

                if ($type !== 'lesson') {
                    $entry['passing_required'] = true;
                }

                $missing[] = $entry;
            }
        }

        return $missing;
    }

    public function isUnitCompleted(Unit $unit, int $userId): bool
    {
        $unitContents = $this->loadAllUnitContents($unit->id);

        foreach ($unitContents as $uc) {
            $isCompleted = match ($uc->contentable_type) {
                'lesson' => $uc->contentable->isCompletedBy($userId),
                'assignment' => $this->isAssignmentPassed($uc->contentable, $userId),
                'quiz' => $this->isQuizPassed($uc->contentable, $userId),
                default => false,
            };

            if (! $isCompleted) {
                return false;
            }
        }

        return true;
    }

    public function getUnitProgress(Unit $unit, int $userId): array
    {
        $unitContents = $this->loadAllUnitContents($unit->id);

        $totalItems = $unitContents->count();
        $completedItems = 0;

        foreach ($unitContents as $uc) {
            $isCompleted = match ($uc->contentable_type) {
                'lesson' => $uc->contentable->isCompletedBy($userId),
                'assignment' => $this->isAssignmentPassed($uc->contentable, $userId),
                'quiz' => $this->isQuizPassed($uc->contentable, $userId),
                default => false,
            };

            if ($isCompleted) {
                $completedItems++;
            }
        }

        return [
            'total' => $totalItems,
            'completed' => $completedItems,
            'percentage' => $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 2) : 0,
        ];
    }

    public function getUnitCompletionCounts(int $unitId, int $enrollmentId, int $userId): array
    {
        $lessonIds = \Modules\Schemes\Models\Lesson::where('unit_id', $unitId)
            ->where('status', 'published')
            ->pluck('id');

        $quizIds = \Modules\Learning\Models\Quiz::where('unit_id', $unitId)
            ->where('status', \Modules\Learning\Enums\QuizStatus::Published)
            ->pluck('id');

        $assignmentIds = \Modules\Learning\Models\Assignment::where('unit_id', $unitId)
            ->where('status', \Modules\Learning\Enums\AssignmentStatus::Published)
            ->pluck('id');

        $totalItems = $lessonIds->count() + $quizIds->count() + $assignmentIds->count();

        $completedLessons = $lessonIds->isNotEmpty()
            ? \Modules\Enrollments\Models\LessonProgress::where('enrollment_id', $enrollmentId)
                ->whereIn('lesson_id', $lessonIds)
                ->where('status', \Modules\Enrollments\Enums\ProgressStatus::Completed)
                ->count()
            : 0;

        $completedQuizzes = $quizIds->isNotEmpty()
            ? \Modules\Learning\Models\QuizSubmission::where('user_id', $userId)
                ->whereIn('quiz_id', $quizIds)
                ->whereIn('status', ['graded', 'released'])
                ->whereNotNull('final_score')
                ->whereRaw('final_score >= (select passing_grade from quizzes where quizzes.id = quiz_submissions.quiz_id)')
                ->distinct('quiz_id')
                ->count('quiz_id')
            : 0;

        $completedAssignments = $assignmentIds->isNotEmpty()
            ? \Modules\Learning\Models\Submission::where('user_id', $userId)
                ->whereIn('assignment_id', $assignmentIds)
                ->where('status', \Modules\Learning\Enums\SubmissionStatus::Graded)
                ->whereRaw('score >= (select COALESCE(passing_grade, max_score * 0.6) from assignments where assignments.id = submissions.assignment_id)')
                ->distinct('assignment_id')
                ->count('assignment_id')
            : 0;

        return [
            'total_items'     => $totalItems,
            'completed_items' => $completedLessons + $completedQuizzes + $completedAssignments,
        ];
    }

    public function getCourseCompletionCounts(int $courseId, int $enrollmentId, int $userId): Collection
    {
        $unitIds = Unit::where('course_id', $courseId)
            ->orderBy('order')
            ->pluck('id');

        $result = collect();
        foreach ($unitIds as $unitId) {
            $result[$unitId] = $this->getUnitCompletionCounts((int) $unitId, $enrollmentId, $userId);
        }

        return $result;
    }

    public function getCourseProgressInfo(int $courseId, int $enrollmentId, int $userId): array
    {
        $courseProgress = \Modules\Enrollments\Models\CourseProgress::where('enrollment_id', $enrollmentId)->first();

        $totalLessons = \Modules\Schemes\Models\Lesson::whereHas('unit', fn ($q) => $q->where('course_id', $courseId))
            ->where('status', 'published')
            ->count();

        $totalQuizzes = \Modules\Learning\Models\Quiz::whereHas('unit', fn ($q) => $q->where('course_id', $courseId))
            ->where('status', \Modules\Learning\Enums\QuizStatus::Published)
            ->count();

        $totalAssignments = \Modules\Learning\Models\Assignment::whereHas('unit', fn ($q) => $q->where('course_id', $courseId))
            ->where('status', \Modules\Learning\Enums\AssignmentStatus::Published)
            ->count();

        $totalContent = $totalLessons + $totalQuizzes + $totalAssignments;

        if (! $courseProgress || $totalContent === 0) {
            return [
                'percentage'           => 0,
                'completed_items'      => 0,
                'total_items'          => $totalContent,
                'last_accessed_lesson' => null,
                'last_accessed_unit'   => null,
            ];
        }

        $completedLessons = \Modules\Enrollments\Models\LessonProgress::where('enrollment_id', $enrollmentId)
            ->where('status', \Modules\Enrollments\Enums\ProgressStatus::Completed)
            ->count();

        $completedQuizzes = \Modules\Learning\Models\QuizSubmission::where('user_id', $userId)
            ->whereHas('quiz', fn ($q) => $q
                ->whereHas('unit', fn ($u) => $u->where('course_id', $courseId))
                ->where('status', \Modules\Learning\Enums\QuizStatus::Published)
            )
            ->whereNotNull('final_score')
            ->whereIn('status', ['graded', 'released'])
            ->whereRaw('final_score >= (select passing_grade from quizzes where quizzes.id = quiz_submissions.quiz_id)')
            ->distinct('quiz_id')
            ->count('quiz_id');

        $completedAssignments = \Modules\Learning\Models\Submission::where('user_id', $userId)
            ->where('status', \Modules\Learning\Enums\SubmissionStatus::Graded)
            ->whereHas('assignment', fn ($q) => $q
                ->whereHas('unit', fn ($u) => $u->where('course_id', $courseId))
                ->where('status', \Modules\Learning\Enums\AssignmentStatus::Published)
            )
            ->whereRaw('score >= (select COALESCE(passing_grade, max_score * 0.6) from assignments where assignments.id = submissions.assignment_id)')
            ->distinct('assignment_id')
            ->count('assignment_id');

        $completedItems = $completedLessons + $completedQuizzes + $completedAssignments;
        $percentage     = $totalContent > 0 ? round(($completedItems / $totalContent) * 100, 2) : 0;

        $lastLessonProgress = \Modules\Enrollments\Models\LessonProgress::where('enrollment_id', $enrollmentId)
            ->orderBy('updated_at', 'desc')
            ->first();

        $lastLesson = null;
        $lastUnit   = null;

        if ($lastLessonProgress?->lesson_id) {
            $lesson = \Modules\Schemes\Models\Lesson::with('unit')->find($lastLessonProgress->lesson_id);
            if ($lesson) {
                $lastLesson = ['id' => $lesson->id, 'title' => $lesson->title, 'slug' => $lesson->slug];
                $lastUnit   = $lesson->unit
                    ? ['id' => $lesson->unit->id, 'title' => $lesson->unit->title, 'slug' => $lesson->unit->slug]
                    : null;
            }
        }

        return [
            'percentage'           => $percentage,
            'completed_items'      => $completedItems,
            'total_items'          => $totalContent,
            'last_accessed_lesson' => $lastLesson,
            'last_accessed_unit'   => $lastUnit,
        ];
    }

    public function getUnitContentOrder(Unit $unit): array
    {
        $unitContents = $this->loadAllUnitContents($unit->id);

        return $unitContents->map(fn (UnitContent $uc) => [
            'type' => $uc->contentable_type,
            'id' => $uc->contentable_id,
            'title' => $uc->contentable?->title,
            'order' => $uc->order,
        ])->values()->toArray();
    }

    private function loadUnitContentsBefore(int $unitId, int $beforeOrder): Collection
    {
        $unitContents = UnitContent::forUnit($unitId)
            ->beforeOrder($beforeOrder)
            ->orderBy('order')
            ->get();

        return $this->loadContentableModels($unitContents);
    }

    private function loadAllUnitContents(int $unitId): Collection
    {
        $unitContents = UnitContent::forUnit($unitId)
            ->orderBy('order')
            ->get();

        return $this->loadContentableModels($unitContents);
    }

    private function loadContentableModels(Collection $unitContents): Collection
    {
        $grouped = $unitContents->groupBy('contentable_type');

        foreach ($grouped as $type => $items) {
            $ids = $items->pluck('contentable_id')->toArray();
            $modelClass = Relation::getMorphedModel($type);

            if ($modelClass) {
                $models = $modelClass::withoutGlobalScopes()
                    ->whereIn('id', $ids)
                    ->whereNull('deleted_at')
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $item->setRelation('contentable', $models->get($item->contentable_id));
                }
            }
        }

        return $unitContents->filter(fn ($uc) => $uc->contentable !== null);
    }

    private function checkContentCompletion(Collection $unitContents, int $userId, ?Unit $unit = null): array
    {
        $missing = [];

        foreach ($unitContents as $uc) {
            $model = $uc->contentable;
            $type = $uc->contentable_type;

            $isPublished = match ($type) {
                'lesson' => ($model->status?->value ?? $model->status) === 'published',
                'assignment' => $model->status === \Modules\Learning\Enums\AssignmentStatus::Published,
                'quiz' => $model->status === \Modules\Learning\Enums\QuizStatus::Published,
                default => false,
            };

            if (! $isPublished) {
                continue;
            }

            $isCompleted = match ($type) {
                'lesson' => $model->isCompletedBy($userId),
                'assignment' => $this->isAssignmentPassed($model, $userId),
                'quiz' => $this->isQuizPassed($model, $userId),
                default => false,
            };

            if (! $isCompleted) {
                $missing[] = [
                    'type' => $type,
                    'id' => $model->id,
                    'title' => $model->title,
                    'slug' => $model->slug ?? null,
                    'unit_title' => $unit?->title,
                ];
            }
        }

        return $missing;
    }

    private function isAssignmentPassed(Assignment $assignment, int $userId): bool
    {
        $highestSubmission = $assignment->submissions()
            ->where('user_id', $userId)
            ->whereIn('status', ['graded'])
            ->orderByDesc('score')
            ->first();

        if (! $highestSubmission) {
            return false;
        }

        $passingScore = $assignment->passing_grade ?? ($assignment->max_score * 0.6);

        return $highestSubmission->score >= $passingScore;
    }

    private function isQuizPassed(Quiz $quiz, int $userId): bool
    {
        $highestSubmission = $quiz->submissions()
            ->where('user_id', $userId)
            ->whereIn('status', ['graded', 'released'])
            ->whereNotNull('final_score')
            ->orderByDesc('final_score')
            ->first();

        if (! $highestSubmission) {
            return false;
        }

        return $highestSubmission->final_score >= $quiz->passing_grade;
    }
}
