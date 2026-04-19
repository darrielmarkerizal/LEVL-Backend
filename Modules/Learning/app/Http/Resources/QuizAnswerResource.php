<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizAnswerResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $request->user();
        $isInstructor = $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Instructor']);

        $data = [
            'id' => $this->id,
            'quiz_question_id' => $this->quiz_question_id,
            'content' => $this->content,
            'selected_options' => $this->selected_options,
            'score' => $this->score,
            'feedback' => $this->feedback,
        ];

        
        if ($isInstructor) {
            $data['is_auto_graded'] = $this->is_auto_graded;
        }

        
        if ($this->relationLoaded('question')) {
            $data['question'] = new QuizQuestionResource($this->question);
        }

        return $data;
    }
}
