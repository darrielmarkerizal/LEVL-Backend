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
            'instructions' => $this->resource->description,
            'submission_type' => $this->resource->submission_type?->value ?? $this->resource->submission_type,
            'max_score' => $this->resource->max_score,
            'passing_grade' => $this->resource->passing_grade,
            'review_mode' => $this->resource->review_mode?->value ?? $this->resource->review_mode,
            'accepted_formats' => $this->acceptedFormats(),
            'max_file_size' => $this->maxFileSizeInMb(),
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
            'attachments' => $attachmentFiles,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }

    private function toInstructorArray(): array
    {
        $attachmentFiles = $this->mapAttachmentFiles();

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'instructions' => $this->resource->description,
            'submission_type' => $this->resource->submission_type?->value ?? $this->resource->submission_type,
            'max_score' => $this->resource->max_score,
            'passing_grade' => $this->resource->passing_grade,
            'review_mode' => $this->resource->review_mode?->value ?? $this->resource->review_mode,
            'status' => $this->resource->status?->value ?? $this->resource->status,
            'accepted_formats' => $this->acceptedFormats(),
            'max_file_size' => $this->maxFileSizeInMb(),
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
            'attachments' => $attachmentFiles,
        ];
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
                    'url' => $url,
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
