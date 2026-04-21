<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Learning\Models\Quiz;
use Modules\Schemes\Services\PrerequisiteService;

class QuizEnrichmentService
{
    public function __construct(
        private readonly PrerequisiteService $prerequisiteService
    ) {}

    public function enrichForStudent(LengthAwarePaginator $paginator, int $userId): LengthAwarePaginator
    {
        $paginator->load(['unit:id,slug,course_id', 'unit.course:id,slug']);
        $paginator->loadCount('questions');

        $quizIds = $paginator->pluck('id')->toArray();
        $submissions = $this->getLatestSubmissions($quizIds, $userId);

        
        $xpSources = \Modules\Gamification\Models\XpSource::whereIn('code', [
            'quiz_passed',
            'perfect_score',
        ])->get()->keyBy('code');

        $baseXp = $xpSources['quiz_passed']->xp_amount ?? 0;
        $perfectScoreXp = $xpSources['perfect_score']->xp_amount ?? 0;

        $paginator->getCollection()->transform(function ($item) use ($submissions, $userId, $baseXp, $perfectScoreXp) {
            $submission = $submissions[$item->id] ?? null;
            $submissionData = $this->calculateSubmissionData($item, $submission, $userId);
            $prerequisiteCheck = $this->prerequisiteService->checkQuizAccess($item, $userId);

            return [
                'id' => $item->id,
                'title' => $item->title,
                'order' => $item->order,
                'passing_grade' => $item->passing_grade,
                'max_score' => $item->max_score,
                'auto_grading' => $item->auto_grading,
                'status' => $item->status?->value,
                'unit_slug' => $item->unit->slug ?? null,
                'is_locked' => ! $prerequisiteCheck['accessible'],
                'prerequisites' => [
                    'is_locked' => ! $prerequisiteCheck['accessible'],
                    'is_completed' => $prerequisiteCheck['accessible'],
                ],
                'submission_status' => $submissionData['submission_status'],
                'submission_status_label' => $submissionData['submission_status_label'],
                'score' => $submissionData['score'],
                'submitted_at' => $submissionData['submitted_at'],
                'is_completed' => $submissionData['is_completed'],
                'attempts_used' => $submissionData['attempts_used'],
                'xp_reward' => $baseXp,
                'xp_perfect_bonus' => $perfectScoreXp,
                'questions_count' => $item->questions_count ?? null,
                'scope_type' => $item->getScopeTypeAttribute(),
                'created_at' => $item->created_at?->toIso8601String(),
                'updated_at' => $item->updated_at?->toIso8601String(),
                'creator' => $item->creator ? [
                    'id' => $item->creator->id,
                    'name' => $item->creator->name,
                ] : null,
            ];
        });

        return $paginator;
    }

    private function getLatestSubmissions(array $quizIds, int $userId): array
    {
        return \Modules\Learning\Models\Submission::where('user_id', $userId)
            ->whereIn('assignment_id', $quizIds)
            ->get()
            ->groupBy('assignment_id')
            ->map(fn ($subs) => $subs->sortByDesc('submitted_at')->first())
            ->all();
    }

    private function calculateSubmissionData($quiz, $submission, int $userId): array
    {
        if (! $submission) {
            return [
                'submission_status' => null,
                'submission_status_label' => 'Belum Dikerjakan',
                'score' => null,
                'submitted_at' => null,
                'is_completed' => false,
                'attempts_used' => 0,
            ];
        }

        $passingGrade = $quiz->passing_grade;
        $isPassed = $submission->status->value === 'graded' && $submission->score >= $passingGrade;

        $submissionCount = \Modules\Learning\Models\Submission::where('user_id', $userId)
            ->where('assignment_id', $quiz->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->count();

        return [
            'submission_status' => $submission->status->value,
            'submission_status_label' => $this->getSubmissionStatusLabel($submission, $isPassed),
            'score' => $submission->score,
            'submitted_at' => $submission->submitted_at?->toIso8601String(),
            'is_completed' => $isPassed,
            'attempts_used' => $submissionCount,
        ];
    }

    private function getSubmissionStatusLabel($submission, bool $isPassed): string
    {
        return match ($submission->status->value) {
            'draft' => 'Draft',
            'submitted' => 'Menunggu Penilaian',
            'graded' => $isPassed ? 'Lulus' : 'Tidak Lulus',
            'returned' => 'Dikembalikan',
            default => 'Unknown',
        };
    }

    public function enrichDetailForStudent($quiz, int $userId)
    {
        $quiz->load(['unit:id,slug,course_id', 'unit.course:id,slug', 'creator:id,name', 'media']);
        $quiz->loadCount('questions');

        $submission = \Modules\Learning\Models\Submission::where('user_id', $userId)
            ->where('assignment_id', $quiz->id)
            ->orderByDesc('submitted_at')
            ->first();

        $submissionData = $this->calculateSubmissionData($quiz, $submission, $userId);
        $prerequisiteCheck = $this->prerequisiteService->checkQuizAccess($quiz, $userId);

        
        $xpSources = \Modules\Gamification\Models\XpSource::whereIn('code', [
            'quiz_passed',
            'perfect_score',
        ])->get()->keyBy('code');

        $baseXp = $xpSources['quiz_passed']->xp_amount ?? 0;
        $perfectScoreXp = $xpSources['perfect_score']->xp_amount ?? 0;

        $quiz->is_locked = ! $prerequisiteCheck['accessible'];
        $quiz->submission_status = $submissionData['submission_status'];
        $quiz->submission_status_label = $submissionData['submission_status_label'];
        $quiz->score = $submissionData['score'];
        $quiz->submitted_at = $submissionData['submitted_at'];
        $quiz->is_completed = $submissionData['is_completed'];
        $quiz->attempts_used = $submissionData['attempts_used'];
        $quiz->xp_reward = $baseXp;
        $quiz->xp_perfect_bonus = $perfectScoreXp;
        $quiz->scope_type = $quiz->getScopeTypeAttribute();
        $quiz->status_value = $quiz->status?->value;

        return $quiz;
    }

    public function enrichSingleForStudent(Quiz $quiz, int $userId): array
    {
        $quiz->load(['unit:id,slug,course_id', 'unit.course:id,slug', 'creator:id,name', 'media']);
        $quiz->loadCount('questions');

        $submission = \Modules\Learning\Models\Submission::where('user_id', $userId)
            ->where('assignment_id', $quiz->id)
            ->orderByDesc('submitted_at')
            ->first();

        $submissionData = $this->calculateSubmissionData($quiz, $submission, $userId);
        $prerequisiteCheck = $this->prerequisiteService->checkQuizAccess($quiz, $userId);

        
        $xpSources = \Modules\Gamification\Models\XpSource::whereIn('code', [
            'quiz_passed',
            'perfect_score',
        ])->get()->keyBy('code');

        $baseXp = $xpSources['quiz_passed']->xp_amount ?? 0;
        $perfectScoreXp = $xpSources['perfect_score']->xp_amount ?? 0;

        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'order' => $quiz->order,
            'description' => $quiz->description,
            'passing_grade' => $quiz->passing_grade,
            'max_score' => $quiz->max_score,
            'time_limit_minutes' => $quiz->time_limit_minutes,
            'auto_grading' => $quiz->auto_grading,
            'review_mode' => $quiz->review_mode?->value ?? $quiz->review_mode,
            'status' => $quiz->status?->value,
            'unit_slug' => $quiz->unit->slug ?? null,
            'is_locked' => ! $prerequisiteCheck['accessible'],
            'prerequisites' => [
                'is_locked' => ! $prerequisiteCheck['accessible'],
                'is_completed' => $prerequisiteCheck['accessible'],
            ],
            'submission_status' => $submissionData['submission_status'],
            'submission_status_label' => $submissionData['submission_status_label'],
            'score' => $submissionData['score'],
            'submitted_at' => $submissionData['submitted_at'],
            'is_completed' => $submissionData['is_completed'],
            'attempts_used' => $submissionData['attempts_used'],
            'xp_reward' => $baseXp,
            'xp_perfect_bonus' => $perfectScoreXp,
            'questions_count' => $quiz->questions_count ?? null,
            'scope_type' => $quiz->getScopeTypeAttribute(),
            'attachments' => $quiz->getMedia('attachments')->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'url' => $m->getUrl(),
                'mime_type' => $m->mime_type,
                'size' => $m->size,
            ])->toArray(),
            'creator' => $quiz->creator ? [
                'id' => $quiz->creator->id,
                'name' => $quiz->creator->name,
            ] : null,
            'created_at' => $quiz->created_at?->toIso8601String(),
            'updated_at' => $quiz->updated_at?->toIso8601String(),
        ];
    }

    public function enrichForInstructor(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->load(['unit:id,slug']);
        $paginator->loadCount('questions');

        $paginator->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'order' => $item->order,
                'passing_grade' => $item->passing_grade,
                'max_score' => $item->max_score,
                'status' => $item->status->value,
                'status_label' => $item->status->label(),
                'auto_grading' => $item->auto_grading,
                'unit_slug' => $item->unit->slug ?? null,
                'questions_count' => $item->questions_count ?? null,
                'available_from' => $item->available_from?->toISOString(),
                'scope_type' => $item->getScopeTypeAttribute(),
                'created_at' => $item->created_at?->toISOString(),
            ];
        });

        return $paginator;
    }
}
