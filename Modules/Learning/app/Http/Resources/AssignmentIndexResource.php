<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $assignment = $this->resource;

        return [
            'id' => $assignment->id,
            'title' => $assignment->title,
            'order' => $assignment->order,
            'sequence' => $assignment->unit?->order !== null && $assignment->order !== null
                ? $assignment->unit->order . '.' . $assignment->order
                : null,
            'description' => $assignment->description,
            'submission_type' => $assignment->submission_type?->value ?? $assignment->submission_type,
            'max_score' => $assignment->max_score,
            'status' => $assignment->status?->value ?? $assignment->status,
            'is_available' => $assignment->isAvailable(),
            'created_at' => $assignment->created_at?->toIso8601String(),
            'updated_at' => $assignment->updated_at?->toIso8601String(),

            'creator' => $this->whenLoaded('creator', function () use ($assignment) {
                return [
                    'id' => $assignment->creator->id,
                    'name' => $assignment->creator->name,
                ];
            }),
        ];
    }
}
