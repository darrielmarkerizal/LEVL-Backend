<?php

declare(strict_types=1);

namespace Modules\Learning\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;

class MarkMissingSubmissionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $now = now();
        $markedCount = 0;

        $markedCount += $this->markMissingAssignmentSubmissions($now);
        $markedCount += $this->markMissingQuizSubmissions($now);

        if ($markedCount > 0) {
            Log::info(__('learning::messages.mark_missing_submissions_completed'), [
                'count' => $markedCount,
            ]);
        }
    }

    private function markMissingAssignmentSubmissions(\Carbon\Carbon $now): int
    {
        $overdueAssignments = Assignment::where('status', 'published')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', $now)
            ->with('unit.course')
            ->get();

        if ($overdueAssignments->isEmpty()) {
            return 0;
        }

        $markedCount = 0;
        $assignmentIds = $overdueAssignments->pluck('id')->toArray();

        $existingSubmissions = DB::table('submissions')
            ->whereIn('assignment_id', $assignmentIds)
            ->select('assignment_id', 'user_id')
            ->get()
            ->groupBy('assignment_id')
            ->map(fn ($group) => $group->pluck('user_id')->toArray());

        $nowString = $now->toDateTimeString();
        $inserts = [];

        foreach ($overdueAssignments as $assignment) {
            if (! $assignment->unit || ! $assignment->unit->course) {
                continue;
            }

            $enrolledUserIds = DB::table('enrollments')
                ->where('course_id', $assignment->unit->course_id)
                ->where('status', 'active')
                ->pluck('user_id')
                ->toArray();

            $submittedUserIds = $existingSubmissions->get($assignment->id, []);

            $missingUserIds = array_diff($enrolledUserIds, $submittedUserIds);

            foreach ($missingUserIds as $userId) {
                $inserts[] = [
                    'assignment_id' => $assignment->id,
                    'user_id' => $userId,
                    'status' => 'missing',
                    'state' => 'pending_grading',
                    'created_at' => $nowString,
                    'updated_at' => $nowString,
                ];
                $markedCount++;
            }
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('submissions')->insert($chunk);
        }

        return $markedCount;
    }

    private function markMissingQuizSubmissions(\Carbon\Carbon $now): int
    {
        $overdueQuizzes = Quiz::where('status', 'published')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', $now)
            ->with('unit.course')
            ->get();

        if ($overdueQuizzes->isEmpty()) {
            return 0;
        }

        $markedCount = 0;
        $quizIds = $overdueQuizzes->pluck('id')->toArray();

        $existingSubmissions = DB::table('quiz_submissions')
            ->whereIn('quiz_id', $quizIds)
            ->select('quiz_id', 'user_id')
            ->get()
            ->groupBy('quiz_id')
            ->map(fn ($group) => $group->pluck('user_id')->toArray());

        $nowString = $now->toDateTimeString();
        $inserts = [];

        foreach ($overdueQuizzes as $quiz) {
            if (! $quiz->unit || ! $quiz->unit->course) {
                continue;
            }

            $enrolledUserIds = DB::table('enrollments')
                ->where('course_id', $quiz->unit->course_id)
                ->where('status', 'active')
                ->pluck('user_id')
                ->toArray();

            $submittedUserIds = $existingSubmissions->get($quiz->id, []);

            $missingUserIds = array_diff($enrolledUserIds, $submittedUserIds);

            foreach ($missingUserIds as $userId) {
                $inserts[] = [
                    'quiz_id' => $quiz->id,
                    'user_id' => $userId,
                    'status' => 'missing',
                    'created_at' => $nowString,
                    'updated_at' => $nowString,
                ];
                $markedCount++;
            }
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('quiz_submissions')->insert($chunk);
        }

        return $markedCount;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('MarkMissingSubmissionsJob failed', [
            'error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array
    {
        return ['mark-missing-submissions'];
    }
}
