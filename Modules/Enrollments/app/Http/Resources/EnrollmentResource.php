<?php

declare(strict_types=1);

namespace Modules\Enrollments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Calculate progress
        $progress = 0;

        // If status is pending, progress is 0
        if ($this->status->value === 'pending') {
            $progress = 0;
        } else {
            // Get progress from courseProgress relationship
            if ($this->relationLoaded('courseProgress') && $this->courseProgress) {
                $progress = round($this->courseProgress->progress_percent ?? 0, 2);
            }
        }

        return [
            'id' => $this->id,
            'status' => $this->status,
            'progress' => $progress,
            'enrolled_at' => $this->enrolled_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            'user' => $this->whenLoaded('user', function () {
                $avatarUrl = null;
                if (is_object($this->user) && method_exists($this->user, 'getFirstMedia')) {
                    $avatarUrl = $this->user->getFirstMedia('avatar')?->getUrl();
                }

                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'username' => $this->user->username ?? null,
                    'avatar_url' => $avatarUrl,
                ];
            }),

            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course->id,
                    'title' => $this->course->title,
                    'slug' => $this->course->slug,
                    'code' => $this->course->code ?? null,
                ];
            }),

            'completed_units' => $this->whenLoaded('unitProgress', function () {
                $total = $this->unitProgress->count();
                $completed = $this->unitProgress->where('status', 'completed')->count();

                return [
                    'completed' => $completed,
                    'total' => $total,
                    'text' => "{$completed} of {$total}",
                ];
            }),

            'assignments' => $this->whenLoaded('assignmentSubmissions', function () {
                $submitted = $this->assignmentSubmissions->whereIn('status', ['submitted', 'graded', 'late'])->count();
                $graded = $this->assignmentSubmissions->where('status', 'graded')->count();

                return [
                    'submitted' => $submitted,
                    'graded' => $graded,
                    'text' => "{$submitted} Submitted, {$graded} Graded",
                ];
            }),

            'quizzes' => $this->whenLoaded('quizSubmissions', function () {
                $gradedSubmissions = $this->quizSubmissions->where('status', 'graded');
                $passed = $gradedSubmissions->where('score', '>=', 70)->count();
                $avgScore = $gradedSubmissions->avg('score');

                return [
                    'passed' => $passed,
                    'average_score' => round($avgScore ?? 0, 0),
                    'text' => "{$passed} Passed (Avg Score: ".round($avgScore ?? 0, 0).')',
                ];
            }),
        ];
    }
}
