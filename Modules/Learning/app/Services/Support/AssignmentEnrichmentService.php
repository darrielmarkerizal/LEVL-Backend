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
        $paginator->load(['unit:id,slug,course_id', 'unit.course:id,slug']);

        $assignmentIds = $paginator->pluck('id')->toArray();
        $submissions = $this->getLatestSubmissions($assignmentIds, $userId);

        $paginator->getCollection()->transform(function ($item) use ($submissions, $userId) {
            $submission = $submissions[$item->id] ?? null;
            $submissionData = $this->calculateSubmissionData($item, $submission, $userId);
            $prerequisiteCheck = $this->prerequisiteService->checkAssignmentAccess($item, $userId);

            return [
                'id' => $item->id,
                'title' => $item->title,
                'submission_type' => $item->submission_type->value,
                'max_score' => $item->max_score,
                'passing_grade' => $item->passing_grade,
                'status' => $item->status->value,
                'is_locked' => ! $prerequisiteCheck['accessible'],
                'unit_slug' => $item->unit->slug ?? null,
                'submission_status' => $submissionData['submission_status'],
                'submission_status_label' => $submissionData['submission_status_label'],
                'score' => $submissionData['score'],
                'submitted_at' => $submissionData['submitted_at'],
                'is_completed' => $submissionData['is_completed'],
                'attempts_used' => $submissionData['attempts_used'],
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
        $paginator->load(['unit:id,slug', 'creator:id,name']);

        $paginator->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'submission_type' => $item->submission_type->value,
                'max_score' => $item->max_score,
                'status' => $item->status->value,
                'is_available' => $item->isAvailable(),
                'unit_slug' => $item->unit->slug ?? null,
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
            ->all();
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
                'attempts_used' => 0,
            ];
        }

        $passingGrade = $assignment->passing_grade;
        $isPassed = $submission->status->value === 'graded' && $submission->score >= $passingGrade;

        $submissionCount = Submission::where('user_id', $userId)
            ->where('assignment_id', $assignment->id)
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

    public function enrichSingleForStudent(Assignment $assignment, int $userId): array
    {
        $assignment->load(['unit:id,slug,course_id', 'unit.course:id,slug', 'creator:id,name', 'media']);

        $submission = Submission::where('user_id', $userId)
            ->where('assignment_id', $assignment->id)
            ->orderByDesc('submitted_at')
            ->first();

        $submissionData = $this->calculateSubmissionData($assignment, $submission, $userId);
        $prerequisiteCheck = $this->prerequisiteService->checkAssignmentAccess($assignment, $userId);

        return [
            'id' => $assignment->id,
            'title' => $assignment->title,
            'description' => $assignment->description,
            'submission_type' => $assignment->submission_type->value,
            'max_score' => $assignment->max_score,
            'passing_grade' => $assignment->passing_grade,
            'status' => $assignment->status->value,
            'is_locked' => ! $prerequisiteCheck['accessible'],
            'unit_slug' => $assignment->unit->slug ?? null,
            'submission_status' => $submissionData['submission_status'],
            'submission_status_label' => $submissionData['submission_status_label'],
            'score' => $submissionData['score'],
            'submitted_at' => $submissionData['submitted_at'],
            'is_completed' => $submissionData['is_completed'],
            'attempts_used' => $submissionData['attempts_used'],
            'attachments' => $assignment->getMedia('attachments')->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'size' => $media->size,
                'mime_type' => $media->mime_type,
            ])->toArray(),
            'created_at' => $assignment->created_at?->toIso8601String(),
            'updated_at' => $assignment->updated_at?->toIso8601String(),
            'creator' => $assignment->creator ? [
                'id' => $assignment->creator->id,
                'name' => $assignment->creator->name,
            ] : null,
        ];
    }
}
