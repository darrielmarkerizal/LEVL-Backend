<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'order' => $this->order,
            'sequence' => $this->sequence(),
            'passing_grade' => $this->passing_grade,
            'max_score' => $this->max_score,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'auto_grading' => $this->auto_grading,
            'questions_count' => $this->when(
                $this->relationLoaded('questions'),
                fn () => $this->questions->count()
            ),
            'available_from' => $this->available_from?->toISOString(),
            'scope_type' => $this->scope_type,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function sequence(): ?string
    {
        $unitOrder = $this->unit?->order;
        $elementOrder = $this->order;

        if ($unitOrder === null || $elementOrder === null) {
            return null;
        }

        return $unitOrder . '.' . $elementOrder;
    }
}
