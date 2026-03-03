<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Schemes\Services\PrerequisiteService;

class QuizEnrichmentService
{
    public function __construct(
        private readonly PrerequisiteService $prerequisiteService
    ) {}

    public function enrichForStudent(LengthAwarePaginator $paginator, int $userId): LengthAwarePaginator
    {
        $paginator->load('lesson.unit:id,slug');

        $paginator->getCollection()->transform(function ($item) use ($userId) {
            $prerequisiteCheck = $this->prerequisiteService->checkQuizAccess($item, $userId);

            return [
                'id' => $item->id,
                'title' => $item->title,
                'passing_grade' => $item->passing_grade,
                'max_score' => $item->max_score,
                'auto_grading' => $item->auto_grading,
                'is_locked' => ! $prerequisiteCheck['accessible'],
                'lesson_slug' => $item->lesson?->slug,
                'unit_slug' => $item->lesson?->unit?->slug,
                'questions_count' => $item->relationLoaded('questions') ? $item->questions->count() : null,
                'scope_type' => $item->getScopeTypeAttribute(),
                'created_at' => $item->created_at?->toISOString(),
            ];
        });

        return $paginator;
    }

    public function enrichForInstructor(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->load('lesson.unit:id,slug');

        $paginator->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'passing_grade' => $item->passing_grade,
                'max_score' => $item->max_score,
                'status' => $item->status->value,
                'status_label' => $item->status->label(),
                'auto_grading' => $item->auto_grading,
                'lesson_slug' => $item->lesson?->slug,
                'unit_slug' => $item->lesson?->unit?->slug,
                'questions_count' => $item->relationLoaded('questions') ? $item->questions->count() : null,
                'available_from' => $item->available_from?->toISOString(),
                'deadline_at' => $item->deadline_at?->toISOString(),
                'scope_type' => $item->getScopeTypeAttribute(),
                'created_at' => $item->created_at?->toISOString(),
            ];
        });

        return $paginator;
    }
}
