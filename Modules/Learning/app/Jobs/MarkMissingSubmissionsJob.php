<?php

declare(strict_types=1);

namespace Modules\Learning\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;

class MarkMissingSubmissionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(): void
    {
        $now = now();
        $markedCount = 0;

        $overdueAssignments = Assignment::where('status', 'published')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', $now)
            ->get();

        foreach ($overdueAssignments as $assignment) {
            $enrolledUsers = $assignment->unit->course->enrollments()
                ->where('status', 'active')
                ->pluck('user_id');

            foreach ($enrolledUsers as $userId) {
                $hasSubmission = Submission::where('assignment_id', $assignment->id)
                    ->where('user_id', $userId)
                    ->exists();

                if (! $hasSubmission) {
                    Submission::create([
                        'assignment_id' => $assignment->id,
                        'user_id' => $userId,
                        'status' => 'missing',
                        'state' => 'pending_grading',
                    ]);
                    $markedCount++;
                }
            }
        }

        $overdueQuizzes = Quiz::where('status', 'published')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', $now)
            ->get();

        foreach ($overdueQuizzes as $quiz) {
            $enrolledUsers = $quiz->unit->course->enrollments()
                ->where('status', 'active')
                ->pluck('user_id');

            foreach ($enrolledUsers as $userId) {
                $hasSubmission = QuizSubmission::where('quiz_id', $quiz->id)
                    ->where('user_id', $userId)
                    ->exists();

                if (! $hasSubmission) {
                    QuizSubmission::create([
                        'quiz_id' => $quiz->id,
                        'user_id' => $userId,
                        'status' => 'missing',
                    ]);
                    $markedCount++;
                }
            }
        }

        if ($markedCount > 0) {
            Log::info(__('learning::messages.mark_missing_submissions_completed'), [
                'count' => $markedCount,
            ]);
        }
    }

    public function tags(): array
    {
        return ['mark-missing-submissions'];
    }
}
