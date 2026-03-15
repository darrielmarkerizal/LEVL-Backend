<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
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
            'id' => $this->id,
            'attempt_number' => $this->attempt_number,
            'status' => $this->status,
            'score' => $this->score,
            'submitted_at' => $this->submitted_at,
            'graded_at' => $this->status?->value === 'graded' ? $this->graded_at : null,
            'assignment' => $this->whenLoaded('assignment', function () {
                return [
                    'id' => $this->assignment->id,
                    'title' => $this->assignment->title,
                ];
            }),
            'files' => $this->getMedia('submission_files')->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'size' => $media->size,
                'mime_type' => $media->mime_type,
            ]),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
        ];
    }

    private function toInstructorArray(): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'user_id' => $this->user_id,
            'enrollment_id' => $this->enrollment_id,
            'attempt_number' => $this->attempt_number,
            'status' => $this->status,
            'state' => $this->state?->value,
            'score' => $this->score,
            'answer_text' => $this->answer_text,
            'question_set' => $this->question_set,
            'submitted_at' => $this->submitted_at,
            'graded_at' => $this->status?->value === 'graded' ? $this->graded_at : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_highest' => $this->when(isset($this->is_highest), $this->is_highest),
            'assignment' => $this->whenLoaded('assignment'),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'enrollment' => $this->whenLoaded('enrollment'),
            'files' => $this->getMedia('submission_files')->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'size' => $media->size,
                'mime_type' => $media->mime_type,
            ]),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            'grade' => $this->whenLoaded('grade'),
        ];
    }
}
