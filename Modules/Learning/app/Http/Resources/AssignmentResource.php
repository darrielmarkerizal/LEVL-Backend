<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Learning\Models\Assignment;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $assignment = $this->resource;

        return [
            'id' => $assignment->id,
            'title' => $assignment->title,
            'description' => $assignment->description,
            'submission_type' => $assignment->submission_type?->value ?? $assignment->submission_type,
            'max_score' => $assignment->max_score,
            'max_attempts' => $assignment->max_attempts,
            'cooldown_minutes' => $assignment->cooldown_minutes,
            'retake_enabled' => $assignment->retake_enabled,
            'review_mode' => $assignment->review_mode?->value ?? $assignment->review_mode,
            'status' => $assignment->status?->value ?? $assignment->status,
            'allow_resubmit' => $assignment->allow_resubmit,
            'lesson_slug' => $assignment->lesson?->slug,
            'unit_slug' => $assignment->lesson?->unit?->slug,
            'course_slug' => $assignment->lesson?->unit?->course?->slug,
            'is_available' => $assignment->isAvailable(),
            'created_at' => $assignment->created_at?->toIso8601String(),
            'updated_at' => $assignment->updated_at?->toIso8601String(),

            
            'creator' => $this->whenLoaded('creator', function () use ($assignment) {
                return [
                    'id' => $assignment->creator->id,
                    'name' => $assignment->creator->name,
                    'email' => $assignment->creator->email,
                ];
            }),
            'questions_count' => $this->when(
                $assignment->questions_count !== null,
                $assignment->questions_count
            ),
            'prerequisites' => $this->whenLoaded('prerequisites', function () use ($assignment) {
                return $assignment->prerequisites->map(function ($prereq) {
                    return [
                        'id' => $prereq->id,
                        'title' => $prereq->title,
                    ];
                });
            }),
            'attachments' => $assignment->getMedia('attachments')->map(function ($media) {
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
