<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Illuminate\Support\Collection;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;

class PrerequisiteService
{
    public function checkLessonAccess(Lesson $lesson, int $userId): array
    {
        $previousLessons = Lesson::where('unit_id', $lesson->unit_id)
            ->where('order', '<', $lesson->order)
            ->orderBy('order')
            ->get();

        $missing = [];

        foreach ($previousLessons as $prevLesson) {
            if (! $prevLesson->isCompletedBy($userId)) {
                $missing[] = [
                    'type' => 'lesson',
                    'id' => $prevLesson->id,
                    'title' => $prevLesson->title,
                    'order' => $prevLesson->order,
                ];
            }
        }

        return [
            'accessible' => empty($missing),
            'missing' => $missing,
        ];
    }

    public function checkAssignmentAccess(Assignment $assignment, int $userId): array
    {
        $unitId = $this->getAssignmentUnitId($assignment);

        if (! $unitId) {
            return ['accessible' => true, 'missing' => []];
        }

        $allContent = $this->getUnitContentBeforeAssignment($unitId, $assignment);
        $missing = $this->checkContentCompletion($allContent, $userId);

        return [
            'accessible' => empty($missing),
            'missing' => $missing,
        ];
    }

    public function checkQuizAccess(Quiz $quiz, int $userId): array
    {
        $unitId = $this->getQuizUnitId($quiz);

        if (! $unitId) {
            return ['accessible' => true, 'missing' => []];
        }

        $allContent = $this->getUnitContentBeforeQuiz($unitId, $quiz);
        $missing = $this->checkContentCompletion($allContent, $userId);

        return [
            'accessible' => empty($missing),
            'missing' => $missing,
        ];
    }

    public function checkUnitAccess(Unit $unit, int $userId): array
    {
        $previousUnit = Unit::where('course_id', $unit->course_id)
            ->where('order', '<', $unit->order)
            ->orderBy('order', 'desc')
            ->first();

        if (! $previousUnit) {
            return ['accessible' => true, 'missing' => []];
        }

        if (! $this->isUnitCompleted($previousUnit, $userId)) {
            return [
                'accessible' => false,
                'missing' => [
                    [
                        'type' => 'unit',
                        'id' => $previousUnit->id,
                        'title' => $previousUnit->title,
                        'message' => 'Complete all content in previous unit',
                    ],
                ],
            ];
        }

        return ['accessible' => true, 'missing' => []];
    }

    public function isUnitCompleted(Unit $unit, int $userId): bool
    {
        $lessons = $unit->lessons;
        $assignments = Assignment::forUnit($unit->id)->get();
        $quizzes = Quiz::forUnit($unit->id)->get();

        foreach ($lessons as $lesson) {
            if (! $lesson->isCompletedBy($userId)) {
                return false;
            }
        }

        foreach ($assignments as $assignment) {
            if (! $this->isAssignmentPassed($assignment, $userId)) {
                return false;
            }
        }

        foreach ($quizzes as $quiz) {
            if (! $this->isQuizPassed($quiz, $userId)) {
                return false;
            }
        }

        return true;
    }

    public function getUnitProgress(Unit $unit, int $userId): array
    {
        $lessons = $unit->lessons;
        $assignments = Assignment::forUnit($unit->id)->get();
        $quizzes = Quiz::forUnit($unit->id)->get();

        $totalItems = $lessons->count() + $assignments->count() + $quizzes->count();
        $completedItems = 0;

        foreach ($lessons as $lesson) {
            if ($lesson->isCompletedBy($userId)) {
                $completedItems++;
            }
        }

        foreach ($assignments as $assignment) {
            if ($this->isAssignmentPassed($assignment, $userId)) {
                $completedItems++;
            }
        }

        foreach ($quizzes as $quiz) {
            if ($this->isQuizPassed($quiz, $userId)) {
                $completedItems++;
            }
        }

        return [
            'total' => $totalItems,
            'completed' => $completedItems,
            'percentage' => $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 2) : 0,
        ];
    }

    public function getUnitContentOrder(Unit $unit): array
    {
        $lessons = $unit->lessons()->orderBy('order')->get();
        $assignments = Assignment::forUnit($unit->id)->ordered()->get();
        $quizzes = Quiz::forUnit($unit->id)->ordered()->get();

        $content = collect();

        foreach ($lessons as $lesson) {
            $content->push([
                'type' => 'lesson',
                'id' => $lesson->id,
                'title' => $lesson->title,
                'order' => $lesson->order,
            ]);
        }

        foreach ($assignments as $assignment) {
            $content->push([
                'type' => 'assignment',
                'id' => $assignment->id,
                'title' => $assignment->title,
                'order' => $assignment->order,
            ]);
        }

        foreach ($quizzes as $quiz) {
            $content->push([
                'type' => 'quiz',
                'id' => $quiz->id,
                'title' => $quiz->title,
                'order' => $quiz->order,
            ]);
        }

        return $content->sortBy('order')->values()->toArray();
    }

    private function getUnitContentBeforeAssignment(int $unitId, Assignment $assignment): Collection
    {
        $content = collect();

        $lessons = Lesson::where('unit_id', $unitId)->orderBy('order')->get();
        foreach ($lessons as $lesson) {
            $content->push(['type' => 'lesson', 'model' => $lesson, 'order' => $lesson->order]);
        }

        $assignments = Assignment::forUnit($unitId)
            ->where('order', '<', $assignment->order)
            ->ordered()
            ->get();

        foreach ($assignments as $prevAssignment) {
            $content->push(['type' => 'assignment', 'model' => $prevAssignment, 'order' => $prevAssignment->order]);
        }

        $quizzes = Quiz::forUnit($unitId)
            ->where('order', '<', $assignment->order)
            ->ordered()
            ->get();

        foreach ($quizzes as $quiz) {
            $content->push(['type' => 'quiz', 'model' => $quiz, 'order' => $quiz->order]);
        }

        return $content->sortBy('order');
    }

    private function getUnitContentBeforeQuiz(int $unitId, Quiz $quiz): Collection
    {
        $content = collect();

        $lessons = Lesson::where('unit_id', $unitId)->orderBy('order')->get();
        foreach ($lessons as $lesson) {
            $content->push(['type' => 'lesson', 'model' => $lesson, 'order' => $lesson->order]);
        }

        $assignments = Assignment::forUnit($unitId)
            ->where('order', '<', $quiz->order)
            ->ordered()
            ->get();

        foreach ($assignments as $assignment) {
            $content->push(['type' => 'assignment', 'model' => $assignment, 'order' => $assignment->order]);
        }

        $quizzes = Quiz::forUnit($unitId)
            ->where('order', '<', $quiz->order)
            ->ordered()
            ->get();

        foreach ($quizzes as $prevQuiz) {
            $content->push(['type' => 'quiz', 'model' => $prevQuiz, 'order' => $prevQuiz->order]);
        }

        return $content->sortBy('order');
    }

    private function checkContentCompletion(Collection $content, int $userId): array
    {
        $missing = [];

        foreach ($content as $item) {
            $model = $item['model'];
            $type = $item['type'];

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
                    'slug' => $model->slug,
                ];
            }
        }

        return $missing;
    }

    private function getAssignmentUnitId(Assignment $assignment): ?int
    {
        return $assignment->unit_id;
    }

    private function getQuizUnitId(Quiz $quiz): ?int
    {
        return $quiz->unit_id;
    }

    private function isAssignmentPassed(Assignment $assignment, int $userId): bool
    {
        $latestSubmission = $assignment->submissions()
            ->where('user_id', $userId)
            ->whereIn('status', ['graded'])
            ->latest('submitted_at')
            ->first();

        if (! $latestSubmission) {
            return false;
        }

        $passingScore = $assignment->max_score * 0.6;

        return $latestSubmission->score >= $passingScore;
    }

    private function isQuizPassed(Quiz $quiz, int $userId): bool
    {
        $latestSubmission = $quiz->submissions()
            ->where('user_id', $userId)
            ->where('status', 'graded')
            ->latest('submitted_at')
            ->first();

        if (! $latestSubmission) {
            return false;
        }

        return $latestSubmission->final_score >= $quiz->passing_grade;
    }
}
