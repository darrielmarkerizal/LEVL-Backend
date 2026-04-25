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
            ->where('status', 'graded')
            ->whereNotNull('final_score')
            ->orderByDesc('final_score')
            ->first();

        if (! $highestSubmission) {
            return false;
        }

        return $highestSubmission->final_score >= $quiz->passing_grade;
    }
}
