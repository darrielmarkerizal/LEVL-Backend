<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isStudent = $user && $user->hasRole('Student');

        if ($isStudent) {
            return $this->toStudentArray();
        }

        return $this->toInstructorArray();
    }

    private function toStudentArray(): array
    {
        $attachmentFiles = $this->mapAttachmentFiles();

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'order' => $this->resource->order,
            'sequence' => $this->sequence(),
            'instructions' => $this->resource->description,
            'submission_type' => $this->resource->submission_type?->value ?? $this->resource->submission_type,
            'max_score' => $this->resource->max_score,
            'passing_grade' => $this->resource->passing_grade,
            'review_mode' => $this->resource->review_mode?->value ?? $this->resource->review_mode,
            'is_locked' => $this->when(isset($this->resource->is_locked), $this->resource->is_locked),
            'is_completed' => $this->when(isset($this->resource->is_completed), $this->resource->is_completed),
            'is_submission_completed' => $this->when(isset($this->resource->is_submission_completed), $this->resource->is_submission_completed),
            'accepted_formats' => $this->when(!$this->isTextSubmission(), $this->acceptedFormats()),
            'max_file_size' => $this->when(!$this->isTextSubmission(), $this->maxFileSizeInMb()),
            'grading_scheme' => $this->gradingScheme(),
            'unit_slug' => $this->resource->unit->slug ?? null,
            'course_slug' => $this->resource->unit->course->slug ?? null,
            'unit' => [
                'id' => $this->resource->unit->id ?? null,
                'slug' => $this->resource->unit->slug ?? null,
                'title' => $this->resource->unit->title ?? null,
                'code' => $this->resource->unit->code ?? null,
                'course' => $this->resource->unit?->course ? [
                    'id' => $this->resource->unit->course->id,
                    'slug' => $this->resource->unit->course->slug,
                    'title' => $this->resource->unit->course->title,
                    'code' => $this->resource->unit->course->code,
                ] : null,
            ],
            'attached_files' => $attachmentFiles,

            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }

    private function toInstructorArray(): array
    {
        $attachmentFiles = $this->mapAttachmentFiles();

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'order' => $this->resource->order,
            'sequence' => $this->sequence(),
            'instructions' => $this->resource->description,
            'submission_type' => $this->resource->submission_type?->value ?? $this->resource->submission_type,
            'max_score' => $this->resource->max_score,
            'passing_grade' => $this->resource->passing_grade,
            'review_mode' => $this->resource->review_mode?->value ?? $this->resource->review_mode,
            'status' => $this->resource->status?->value ?? $this->resource->status,
            'accepted_formats' => $this->when(!$this->isTextSubmission(), $this->acceptedFormats()),
            'max_file_size' => $this->when(!$this->isTextSubmission(), $this->maxFileSizeInMb()),
            'grading_scheme' => $this->gradingScheme(),
            'unit_slug' => $this->resource->unit->slug ?? null,
            'course_slug' => $this->resource->unit->course->slug ?? null,
            'unit' => [
                'id' => $this->resource->unit->id ?? null,
                'slug' => $this->resource->unit->slug ?? null,
                'title' => $this->resource->unit->title ?? null,
                'code' => $this->resource->unit->code ?? null,
                'course' => $this->resource->unit?->course ? [
                    'id' => $this->resource->unit->course->id,
                    'slug' => $this->resource->unit->course->slug,
                    'title' => $this->resource->unit->course->title,
                    'code' => $this->resource->unit->course->code,
                ] : null,
            ],
            'is_available' => $this->resource->isAvailable(),
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->resource->creator->id,
                    'name' => $this->resource->creator->name,
                    'email' => $this->resource->creator->email,
                ];
            }),
            'questions_count' => $this->when(
                $this->resource->questions_count !== null,
                $this->resource->questions_count
            ),
            'prerequisites' => $this->whenLoaded('prerequisites', function () {
                return $this->resource->prerequisites->map(function ($prereq) {
                    return [
                        'id' => $prereq->id,
                        'title' => $prereq->title,
                    ];
                });
            }),
            'attached_files' => $attachmentFiles,

        ];
    }

    private function isTextSubmission(): bool
    {
        $type = $this->resource->submission_type;
        return $type === 'text' || $type === \Modules\Learning\Enums\SubmissionType::Text;
    }

    private function acceptedFormats(): array
    {
        return ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.zip', '.jpg', '.jpeg', '.png', '.webp'];
    }

    private function maxFileSizeInMb(): int
    {
        $maxFileSizeInBytes = (int) config('media-library.max_file_size', 52428800);

        return (int) ceil($maxFileSizeInBytes / 1024 / 1024);
    }

    private function gradingScheme(): string
    {
        $maxScore = (int) ($this->resource->max_score ?? 100);

        return "Manual Grading by Instructor (1 - {$maxScore} Points)";
    }

    private function sequence(): ?string
    {
        $unitOrder = $this->resource->unit?->order;
        $elementOrder = $this->resource->order;

        if ($unitOrder === null || $elementOrder === null) {
            return null;
        }

        return $unitOrder . '.' . $elementOrder;
    }

    private function resolveMediaUrl($media): string
    {
        try {
            return $media->getTemporaryUrl(now()->addMinutes(30));
        } catch (Throwable) {
            return $media->getUrl();
        }
    }

    private function mapAttachmentFiles()
    {
        return $this->resource->getMedia('attachments')
            ->filter(fn ($media) => $this->isMediaFileAvailable($media))
            ->map(function ($media) {
                $url = $this->resolveMediaUrl($media);

                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'file_url' => $url,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ];
            })
            ->values();
    }

    private function isMediaFileAvailable($media): bool
    {
        return Storage::disk($media->disk)->exists($media->getPath());
    }
}
