<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;
use Modules\Schemes\Services\PrerequisiteService;

class AssignmentEnrichmentService
{
    public function __construct(
        private readonly PrerequisiteService $prerequisiteService
    ) {}

    public function enrichForStudent(LengthAwarePaginator $paginator, int $userId): LengthAwarePaginator
    {
        $paginator->load('lesson.unit:id,slug');

        $assignmentIds = $paginator->pluck('id')->toArray();
        $submissions = $this->getLatestSubmissions($assignmentIds, $userId);

        $paginator->getCollection()->transform(function ($item) use ($submissions, $userId) {
            $submission = $submissions[$item->id] ?? null;
            $submissionData = $this->calculateSubmissionData($item, $submission, $userId);
            $prerequisiteCheck = $this->prerequisiteService->checkAssignmentAccess($item, $userId);

            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'submission_type' => $item->submission_type->value,
                'max_score' => $item->max_score,
                'passing_grade' => $item->passing_grade,
                'status' => $item->status->value,
                'is_locked' => ! $prerequisiteCheck['accessible'],
                'lesson_slug' => $item->lesson?->slug,
                'unit_slug' => $item->lesson?->unit?->slug,
                'submission_status' => $submissionData['submission_status'],
                'submission_status_label' => $submissionData['submission_status_label'],
                'score' => $submissionData['score'],
                'submitted_at' => $submissionData['submitted_at'],
                'is_completed' => $submissionData['is_completed'],
                'can_retake' => $submissionData['can_retake'],
                'attempts_used' => $submissionData['attempts_used'],
                'max_attempts' => $item->max_attempts,
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

    public function enrichForInstructor(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->load('lesson.unit:id,slug', 'creator:id,name');

        $paginator->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'submission_type' => $item->submission_type->value,
                'max_score' => $item->max_score,
                'status' => $item->status->value,
                'is_available' => $item->isAvailable(),
                'lesson_slug' => $item->lesson?->slug,
                'unit_slug' => $item->lesson?->unit?->slug,
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

    private function getLatestSubmissions(array $assignmentIds, int $userId): array
    {
        return Submission::where('user_id', $userId)
            ->whereIn('assignment_id', $assignmentIds)
            ->get()
            ->groupBy('assignment_id')
            ->map(fn ($subs) => $subs->sortByDesc('submitted_at')->first())
            ->toArray();
    }

    private function calculateSubmissionData(Assignment $assignment, ?Submission $submission, int $userId): array
    {
        if (! $submission) {
            return [
                'submission_status' => null,
                'submission_status_label' => 'Belum Dikerjakan',
                'score' => null,
                'submitted_at' => null,
                'is_completed' => false,
                'can_retake' => false,
                'attempts_used' => 0,
            ];
        }

        $passingGrade = $assignment->passing_grade;
        $isPassed = $submission->status->value === 'graded' && $submission->score >= $passingGrade;
        $isFailed = $submission->status->value === 'graded' && $submission->score < $passingGrade;
        $isWaitingGrade = $submission->status->value === 'submitted';

        $submissionCount = Submission::where('user_id', $userId)
            ->where('assignment_id', $assignment->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->count();

        $canRetake = $assignment->retake_enabled &&
                    $isFailed &&
                    ! $isWaitingGrade &&
                    ($assignment->max_attempts === null || $submissionCount < $assignment->max_attempts);

        return [
            'submission_status' => $submission->status->value,
            'submission_status_label' => $this->getSubmissionStatusLabel($submission, $isPassed),
            'score' => $submission->score,
            'submitted_at' => $submission->submitted_at?->toIso8601String(),
            'is_completed' => $isPassed || ($isFailed && ! $canRetake),
            'can_retake' => $canRetake,
            'attempts_used' => $submissionCount,
        ];
    }

    private function getSubmissionStatusLabel(Submission $submission, bool $isPassed): string
    {
        return match ($submission->status->value) {
            'draft' => 'Draft',
            'submitted' => 'Menunggu Penilaian',
            'graded' => $isPassed ? 'Lulus' : 'Tidak Lulus',
            'returned' => 'Dikembalikan',
            default => 'Unknown',
        };
    }
}
