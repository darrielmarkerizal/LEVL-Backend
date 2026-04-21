<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;
use Modules\Schemes\Services\PrerequisiteService;
use Throwable;

class AssignmentEnrichmentService
{
    public function __construct(
        private readonly PrerequisiteService $prerequisiteService
    ) {
    }

    public function enrichForStudent(LengthAwarePaginator $paginator, int $userId): LengthAwarePaginator
    {
        $paginator->load(['unit:id,slug,course_id', 'unit.course:id,slug']);

        $assignmentIds = $paginator->pluck('id')->toArray();
        $submissions = $this->getLatestSubmissions($assignmentIds, $userId);


        $xpSources = \Modules\Gamification\Models\XpSource::whereIn('code', [
            'assignment_submitted',
            'perfect_score',
        ])->get()->keyBy('code');

        $baseXp = $xpSources['assignment_submitted']->xp_amount ?? 0;
        $perfectScoreXp = $xpSources['perfect_score']->xp_amount ?? 0;

        $paginator->getCollection()->transform(function ($item) use ($submissions, $userId, $baseXp, $perfectScoreXp) {
            $submission = $submissions[$item->id] ?? null;
            $submissionData = $this->calculateSubmissionData($item, $submission, $userId);
            $prerequisiteCheck = $this->prerequisiteService->checkAssignmentAccess($item, $userId);

            return [
                'id' => $item->id,
                'title' => $item->title,
                'order' => $item->order,
                'submission_type' => $item->submission_type->value,
                'max_score' => $item->max_score,
                'passing_grade' => $item->passing_grade,
                'status' => $item->status->value,
                'unit_slug' => $item->unit->slug ?? null,
                'is_locked' => !$prerequisiteCheck['accessible'],
                'submission_status' => $submissionData['submission_status'],
                'submission_status_label' => $submissionData['submission_status_label'],
                'score' => $submissionData['score'],
                'submitted_at' => $submissionData['submitted_at'],
                'is_completed' => $submissionData['is_completed'],
                'attempts_used' => $submissionData['attempts_used'],
                'xp_reward' => $baseXp,
                'xp_perfect_bonus' => $perfectScoreXp,
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
            ->map(fn($subs) => $subs->sortByDesc('submitted_at')->first())
            ->all();
    }

    private function calculateSubmissionData(Assignment $assignment, ?Submission $submission, int $userId): array
    {
        if (!$submission) {
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
        $assignment->load(['unit:id,slug,title,code,course_id', 'unit.course:id,slug,title,code', 'creator:id,name', 'media']);

        $submission = Submission::where('user_id', $userId)
            ->where('assignment_id', $assignment->id)
            ->orderByDesc('submitted_at')
            ->first();

        $submissionData = $this->calculateSubmissionData($assignment, $submission, $userId);
        $prerequisiteCheck = $this->prerequisiteService->checkAssignmentAccess($assignment, $userId);

        $xpSources = \Modules\Gamification\Models\XpSource::whereIn('code', [
            'assignment_submitted',
            'perfect_score',
        ])->get()->keyBy('code');

        $baseXp = $xpSources['assignment_submitted']->xp_amount ?? 0;
        $perfectScoreXp = $xpSources['perfect_score']->xp_amount ?? 0;

        $attachmentFiles = $assignment->getMedia('attachments')
            ->filter(fn($media) => Storage::disk($media->disk)->exists($media->getPath()))
            ->map(function ($media) {
                $url = $this->resolveMediaUrl($media);

                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'file_url' => $url,
                    'url' => $url,
                    'size' => $media->size,
                    'mime_type' => $media->mime_type,
                ];
            })
            ->values()
            ->toArray();

        $maxFileSizeInBytes = (int) config('media-library.max_file_size', 52428800);
        $maxFileSizeInMb = (int) ceil($maxFileSizeInBytes / 1024 / 1024);

        return [
            'id' => $assignment->id,
            'title' => $assignment->title,
            'order' => $assignment->order,
            'description' => $assignment->description,
            'instructions' => $assignment->description,
            'submission_type' => $assignment->submission_type->value,
            'max_score' => $assignment->max_score,
            'passing_grade' => $assignment->passing_grade,
            'status' => $assignment->status->value,
            'accepted_formats' => ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.zip', '.jpg', '.jpeg', '.png', '.webp'],
            'max_file_size' => $maxFileSizeInMb,
            'grading_scheme' => "Manual Grading by Instructor (1 - {$assignment->max_score} Points)",
            'unit_slug' => $assignment->unit->slug ?? null,
            'course_slug' => $assignment->unit->course->slug ?? null,
            'unit' => [
                'id' => $assignment->unit->id ?? null,
                'slug' => $assignment->unit->slug ?? null,
                'title' => $assignment->unit->title ?? null,
                'code' => $assignment->unit->code ?? null,
                'course' => $assignment->unit?->course ? [
                    'id' => $assignment->unit->course->id,
                    'slug' => $assignment->unit->course->slug,
                    'title' => $assignment->unit->course->title,
                    'code' => $assignment->unit->course->code,
                ] : null,
            ],
            'is_locked' => !$prerequisiteCheck['accessible'],
            'submission_status' => $submissionData['submission_status'],
            'submission_status_label' => $submissionData['submission_status_label'],
            'score' => $submissionData['score'],
            'submitted_at' => $submissionData['submitted_at'],
            'is_completed' => $submissionData['is_completed'],
            'attempts_used' => $submissionData['attempts_used'],
            'xp_reward' => $baseXp,
            'xp_perfect_bonus' => $perfectScoreXp,
            'attached_files' => $attachmentFiles,
            'attachments' => $attachmentFiles,
            'creator' => $assignment->creator ? [
                'id' => $assignment->creator->id,
                'name' => $assignment->creator->name,
            ] : null,
            'created_at' => $assignment->created_at?->toIso8601String(),
            'updated_at' => $assignment->updated_at?->toIso8601String(),
        ];
    }

    private function resolveMediaUrl($media): string
    {
        try {
            return $media->getTemporaryUrl(now()->addMinutes(30));
        } catch (Throwable) {
            return $media->getUrl();
        }
    }
}
