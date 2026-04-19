<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionDetailResource extends JsonResource
{
    protected array $visibility = [];

    public function withVisibility(array $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function toArray(Request $request): array
    {
        $this->loadMissing(['assignment', 'answers']);

        return [
            'id' => $this->id,
            'assignment' => [
                'id' => $this->assignment->id,
                'title' => $this->assignment->title,
            ],
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'workflow_state' => $this->state instanceof \BackedEnum ? $this->state->value : $this->state,
            'workflow_state_label' => $this->state?->label(),
            'attempt_number' => $this->attempt_number,
            'score' => $this->score,
            'submitted_at' => $this->submitted_at,
            'graded_at' => $this->status?->value === 'graded' ? $this->graded_at : null,
            'files' => $this->getMedia('submission_files')->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'size' => $media->size,
                'mime_type' => $media->mime_type,
            ]),
            'answers' => $this->answers->map(function ($answer) {
                return (new AnswerDetailResource($answer))->withVisibility($this->visibility);
            }),
        ];
    }
}
