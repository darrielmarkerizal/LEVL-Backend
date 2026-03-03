<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'submission_type' => $this->resource->submission_type?->value ?? $this->resource->submission_type,
            'max_score' => $this->resource->max_score,
            'passing_grade' => $this->resource->passing_grade,
            'review_mode' => $this->resource->review_mode?->value ?? $this->resource->review_mode,
            'unit_slug' => $this->resource->unit->slug ?? null,
            'course_slug' => $this->resource->unit->course->slug ?? null,
            'attachments' => $this->resource->getMedia('attachments')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ];
            }),
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }

    private function toInstructorArray(): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'submission_type' => $this->resource->submission_type?->value ?? $this->resource->submission_type,
            'max_score' => $this->resource->max_score,
            'review_mode' => $this->resource->review_mode?->value ?? $this->resource->review_mode,
            'status' => $this->resource->status?->value ?? $this->resource->status,
            'unit_slug' => $this->resource->unit->slug ?? null,
            'course_slug' => $this->resource->unit->course->slug ?? null,
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
            'attachments' => $this->resource->getMedia('attachments')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ];
            }),
        ];
    }
}
