<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Illuminate\Support\Str;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Schemes\Http\Resources\LessonBlockResource;
use Modules\Schemes\Models\Lesson;

class ContentMetadataService
{
    public function getContentMetadata(int $contentId, string $type): array
    {
        $content = match ($type) {
            'lesson' => Lesson::with(['unit.course', 'blocks' => fn($q) => $q->orderBy('order')])->findOrFail($contentId),
            'assignment' => Assignment::with(['unit.course', 'media'])->findOrFail($contentId),
            'quiz' => Quiz::with('unit.course')->findOrFail($contentId),
            default => throw new \InvalidArgumentException("Invalid content type: {$type}"),
        };

        $result = [
            'id' => $content->id,
            'type' => $type,
            'slug' => $content->slug ?? "{$type}-{$content->id}",
            'title' => $content->title,
            'description' => $content->description ?? null,
            'status' => $content->status ?? 'draft',
            'order' => $content->order ?? null,
            'sequence' => $this->buildSequence($content),
            'unit' => [
                'id' => $content->unit->id,
                'slug' => $content->unit->slug,
                'title' => $content->unit->title,
                'code' => $content->unit->code,
                'course_slug' => $content->unit->course->slug ?? null,
                'course' => $content->unit->course ? [
                    'id' => $content->unit->course->id,
                    'slug' => $content->unit->course->slug,
                    'title' => $content->unit->course->title,
                    'code' => $content->unit->course->code,
                ] : null,
            ],
        ];

        if ($type === 'lesson') {
            $result['slug'] = $content->slug ?? (string) $content->id;
            $result['duration_minutes'] = $content->duration_minutes ?? null;

            if ($content->relationLoaded('blocks')) {
                $result['blocks'] = LessonBlockResource::collection($content->blocks)->resolve();
            }

            $xpSource = \Modules\Gamification\Models\XpSource::where('code', 'lesson_completed')
                ->where('is_active', true)
                ->first();
            $result['xp_reward'] = $xpSource ? $xpSource->xp_amount : 50;
        }

        if ($type === 'assignment' && $content instanceof Assignment) {
            $result = array_merge($result, $this->buildAssignmentMetadata($content));
        }

        if ($type === 'quiz' && $content instanceof Quiz) {
            $result = array_merge($result, $this->buildQuizMetadata($content));
        }

        return $result;
    }

    public function getContentMetadataByIdOnly(int $contentId): array
    {
        $candidates = [];

        $lesson = Lesson::with(['unit.course', 'blocks' => fn($q) => $q->orderBy('order')])->find($contentId);
        if ($lesson) {
            $candidates[] = [
                'model' => $lesson,
                'type' => 'lesson',
                'updated_at' => $lesson->updated_at,
            ];
        }

        $assignment = Assignment::with(['unit.course', 'media'])->find($contentId);
        if ($assignment) {
            $candidates[] = [
                'model' => $assignment,
                'type' => 'assignment',
                'updated_at' => $assignment->updated_at,
            ];
        }

        $quiz = Quiz::with('unit.course')->find($contentId);
        if ($quiz) {
            $candidates[] = [
                'model' => $quiz,
                'type' => 'quiz',
                'updated_at' => $quiz->updated_at,
            ];
        }

        if (empty($candidates)) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Content not found');
        }

        usort($candidates, function ($a, $b) {
            return $b['updated_at'] <=> $a['updated_at'];
        });

        $selected = $candidates[0];
        $content = $selected['model'];
        $type = $selected['type'];

        $result = [
            'id' => $content->id,
            'type' => $type,
            'slug' => $content->slug ?? "{$type}-{$content->id}",
            'title' => $content->title,
            'description' => $content->description ?? null,
            'status' => $content->status ?? 'draft',
            'order' => $content->order ?? null,
            'sequence' => $this->buildSequence($content),
            'unit' => [
                'id' => $content->unit->id,
                'slug' => $content->unit->slug,
                'title' => $content->unit->title,
                'code' => $content->unit->code,
                'course_slug' => $content->unit->course->slug ?? null,
                'course' => $content->unit->course ? [
                    'id' => $content->unit->course->id,
                    'slug' => $content->unit->course->slug,
                    'title' => $content->unit->course->title,
                    'code' => $content->unit->course->code,
                ] : null,
            ],
        ];

        if ($type === 'lesson') {
            $result['slug'] = $content->slug ?? (string) $content->id;
            $result['duration_minutes'] = $content->duration_minutes ?? null;

            if ($content->relationLoaded('blocks')) {
                $result['blocks'] = LessonBlockResource::collection($content->blocks)->resolve();
            }

            $xpSource = \Modules\Gamification\Models\XpSource::where('code', 'lesson_completed')
                ->where('is_active', true)
                ->first();
            $result['xp_reward'] = $xpSource ? $xpSource->xp_amount : 50;
        }

        if ($type === 'assignment' && $content instanceof Assignment) {
            $result = array_merge($result, $this->buildAssignmentMetadata($content));
        }

        if ($type === 'quiz' && $content instanceof Quiz) {
            $result = array_merge($result, $this->buildQuizMetadata($content));
        }

        return $result;
    }

    private function buildAssignmentMetadata(Assignment $assignment): array
    {
        $maxFileSizeInBytes = (int) config('media-library.max_file_size', 52428800);
        $maxFileSizeInMb = (int) ceil($maxFileSizeInBytes / 1024 / 1024);
        $maxScore = (int) ($assignment->max_score ?? 100);

        return [
            'instructions' => $assignment->description,
            'submission_type' => $assignment->submission_type?->value ?? $assignment->submission_type,
            'max_score' => $assignment->max_score,
            'passing_grade' => $assignment->passing_grade,
            'accepted_formats' => ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.zip', '.jpg', '.jpeg', '.png', '.webp'],
            'max_file_size' => $maxFileSizeInMb,
            'grading_scheme' => "Manual Grading by Instructor (1 - {$maxScore} Points)",
            'attached_files' => $assignment->getMedia('attachments')->map(fn ($media) => [
                'id' => $media->id,
                'file_name' => $media->file_name,
                'file_url' => $media->getUrl(),
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ])->values()->toArray(),
            'attachments' => $assignment->getMedia('attachments')->map(fn ($media) => [
                'id' => $media->id,
                'file_name' => $media->file_name,
                'file_url' => $media->getUrl(),
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ])->values()->toArray(),
        ];
    }

    private function buildQuizMetadata(Quiz $quiz): array
    {
        $totalQuestions = (int) $quiz->questions()->count();
        $questions = $quiz->questions()->ordered()->limit(5)->get();
        $totalPoints = (float) ($quiz->max_score ?? 0);

        if ($totalPoints <= 0.0) {
            $totalPoints = (float) $quiz->questions()->sum('weight');
        }

        $pointsPerQuestion = $totalQuestions > 0
            ? round($totalPoints / $totalQuestions, 2)
            : null;

        $hasEssayQuestions = $quiz->questions()
            ->where('type', 'essay')
            ->exists();

        $questionsPreview = $questions
            ->take(5)
            ->map(function ($question) {
                $questionType = $question->type?->value ?? $question->type;
                $typeLabel = $question->type?->label() ?? Str::headline((string) $questionType);

                return [
                    'id' => $question->id,
                    'order' => $question->order,
                    'display_id' => '#'.str_pad((string) $question->order, 2, '0', STR_PAD_LEFT),
                    'question_preview' => Str::limit(trim(strip_tags((string) $question->content)), 80, '...'),
                    'type' => $questionType,
                    'type_label' => $typeLabel,
                    'points' => (float) ($question->weight ?? 0),
                ];
            })
            ->values()
            ->toArray();

        return [
            'total_questions' => $totalQuestions,
            'question_count' => $totalQuestions,
            'total_points' => $totalPoints,
            'points_per_question' => $pointsPerQuestion,
            'has_essay_questions' => $hasEssayQuestions,
            'requires_manual_review' => $hasEssayQuestions,
            'questions_preview' => $questionsPreview,
        ];
    }

    private function buildSequence(object $content): ?string
    {
        $unitOrder = (int) ($content->unit->order ?? 0);
        $contentOrder = (int) ($content->order ?? 0);

        if ($unitOrder > 0 && $contentOrder > 0) {
            return "{$unitOrder}.{$contentOrder}";
        }

        if ($contentOrder > 0) {
            return (string) $contentOrder;
        }

        return null;
    }
}
