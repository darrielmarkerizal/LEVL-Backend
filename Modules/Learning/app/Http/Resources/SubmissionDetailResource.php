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
        $this->loadMissing(['assignment', 'answers', 'files']);

        return [
            'id' => $this->id,
            'assignment' => [
                'id' => $this->assignment->id,
                'title' => $this->assignment->title,
            ],
            'status' => $this->status,
            'attempt_number' => $this->attempt_number,
            'score' => $this->score,
            'submitted_at' => $this->submitted_at,
            'graded_at' => $this->status?->value === 'graded' ? $this->graded_at : null,
            'duration' => $this->duration,
            'duration_formatted' => $this->formatted_duration,
            'files' => $this->files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_name' => $file->file_name,
                    'file_size' => $file->file_size,
                    'file_url' => $file->file_url,
                ];
            }),
            'answers' => $this->answers->map(function ($answer) {
                return (new AnswerDetailResource($answer))->withVisibility($this->visibility);
            }),
        ];
    }
}
