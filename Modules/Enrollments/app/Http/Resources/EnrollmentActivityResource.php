<?php

declare(strict_types=1);

namespace Modules\Enrollments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->occurred_at->format('M d, Y'),
            'time' => $this->occurred_at->format('H:i'),
            'datetime' => $this->occurred_at->toISOString(),
            'description' => $this->title,
            'details' => $this->body,
            'event_type' => $this->event_type,
            'metadata' => $this->metadata,
            'lesson' => $this->whenLoaded('lesson', function () {
                return [
                    'id' => $this->lesson->id,
                    'title' => $this->lesson->title,
                ];
            }),
            'quiz' => $this->whenLoaded('quiz', function () {
                return [
                    'id' => $this->quiz->id,
                    'title' => $this->quiz->title,
                ];
            }),
            'assignment' => $this->whenLoaded('assignment', function () {
                return [
                    'id' => $this->assignment->id,
                    'title' => $this->assignment->title,
                ];
            }),
        ];
    }
}

