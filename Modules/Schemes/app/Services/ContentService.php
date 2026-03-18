<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Modules\Learning\Contracts\Services\AssignmentServiceInterface;
use Modules\Learning\Contracts\Services\QuizServiceInterface;
use Modules\Schemes\Contracts\Services\LessonServiceInterface;
use Modules\Schemes\Models\Unit;

class ContentService
{
    public function __construct(
        private readonly LessonServiceInterface $lessonService,
        private readonly AssignmentServiceInterface $assignmentService,
        private readonly QuizServiceInterface $quizService
    ) {}

    public function createContent(Unit $unit, array $data, int $creatorId): array
    {
        $type = $data['type'];
        unset($data['type']);

        return match ($type) {
            'lesson' => $this->createLesson($unit, $data),
            'assignment' => $this->createAssignment($unit, $data, $creatorId),
            'quiz' => $this->createQuiz($unit, $data, $creatorId),
            default => throw new \InvalidArgumentException("Invalid content type: {$type}"),
        };
    }

    private function createLesson(Unit $unit, array $data): array
    {
        $lesson = $this->lessonService->create($unit, $data);

        return [
            'type' => 'lesson',
            'id' => $lesson->id,
            'slug' => $lesson->slug,
            'title' => $lesson->title,
            'order' => $lesson->order,
            'status' => $lesson->status->value,
            'data' => $lesson,
        ];
    }

    private function createAssignment(Unit $unit, array $data, int $creatorId): array
    {
        $data['unit_slug'] = $unit->slug;
        $assignment = $this->assignmentService->create($data, $creatorId);

        return [
            'type' => 'assignment',
            'id' => $assignment->id,
            'slug' => null,
            'title' => $assignment->title,
            'order' => $assignment->order,
            'status' => $assignment->status->value,
            'data' => $assignment,
        ];
    }

    private function createQuiz(Unit $unit, array $data, int $creatorId): array
    {
        $data['unit_slug'] = $unit->slug;
        $quiz = $this->quizService->create($data, $creatorId);

        return [
            'type' => 'quiz',
            'id' => $quiz->id,
            'slug' => null,
            'title' => $quiz->title,
            'order' => $quiz->order,
            'status' => $quiz->status->value,
            'data' => $quiz,
        ];
    }
}
