<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\LessonCompletion;

class LessonCompletionService
{
    public function markAsCompleted(Lesson $lesson, int $userId): LessonCompletion
    {
        return LessonCompletion::firstOrCreate(
            [
                'lesson_id' => $lesson->id,
                'user_id' => $userId,
            ],
            [
                'completed_at' => now(),
            ]
        );
    }

    public function unmarkAsCompleted(Lesson $lesson, int $userId): bool
    {
        return LessonCompletion::where('lesson_id', $lesson->id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function isCompleted(Lesson $lesson, int $userId): bool
    {
        return $lesson->isCompletedBy($userId);
    }

    public function getUserCompletions(int $userId, int $unitId): array
    {
        return LessonCompletion::whereHas('lesson', function ($query) use ($unitId) {
            $query->where('unit_id', $unitId);
        })
            ->where('user_id', $userId)
            ->with('lesson')
            ->get()
            ->map(fn ($completion) => [
                'lesson_id' => $completion->lesson_id,
                'lesson_title' => $completion->lesson->title,
                'completed_at' => $completion->completed_at,
            ])
            ->toArray();
    }
}
