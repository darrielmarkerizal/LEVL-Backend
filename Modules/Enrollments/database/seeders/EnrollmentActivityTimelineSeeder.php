<?php

declare(strict_types=1);

namespace Modules\Enrollments\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnrollmentActivityTimelineSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('enrollment_activities')) {
            $this->command->warn('enrollment_activities table missing. Run migrations first.');

            return;
        }

        $this->command->info('Rebuilding enrollment activity timeline...');

        DB::table('enrollment_activities')->delete();

        $now = now()->toDateTimeString();
        $lessonTitles = DB::table('lessons')->pluck('title', 'id');
        $quizTitles = DB::table('quizzes')->pluck('title', 'id');
        $quizPassing = DB::table('quizzes')->pluck('passing_grade', 'id');
        $assignmentTitles = DB::table('assignments')->pluck('title', 'id');
        $courseTitles = DB::table('courses')->pluck('title', 'id');

        $insertBatch = [];
        $batchSize = 400;

        DB::table('enrollments')
            ->orderBy('id')
            ->chunk(150, function ($enrollments) use (
                &$insertBatch,
                $batchSize,
                $lessonTitles,
                $quizTitles,
                $quizPassing,
                $assignmentTitles,
                $courseTitles,
                $now
            ) {
                foreach ($enrollments as $enrollment) {
                    $courseTitle = $courseTitles[$enrollment->course_id] ?? 'Course';
                    $events = [];

                    $enrolledAt = $enrollment->enrolled_at ?? $enrollment->created_at;
                    $events[] = [
                        'enrollment_id' => $enrollment->id,
                        'user_id' => $enrollment->user_id,
                        'course_id' => $enrollment->course_id,
                        'event_type' => 'enrolled',
                        'title' => 'Enrolled in course "'.$courseTitle.'"',
                        'body' => null,
                        'metadata' => json_encode(['course_id' => $enrollment->course_id]),
                        'lesson_id' => null,
                        'quiz_id' => null,
                        'assignment_id' => null,
                        'occurred_at' => $enrolledAt,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $lessons = DB::table('lesson_progress')
                        ->where('enrollment_id', $enrollment->id)
                        ->where('status', 'completed')
                        ->get(['lesson_id', 'completed_at', 'updated_at']);

                    foreach ($lessons as $lp) {
                        $lid = (int) $lp->lesson_id;
                        $ltitle = $lessonTitles[$lid] ?? 'Lesson';
                        $occ = $lp->completed_at ?? $lp->updated_at ?? $enrolledAt;
                        $events[] = [
                            'enrollment_id' => $enrollment->id,
                            'user_id' => $enrollment->user_id,
                            'course_id' => $enrollment->course_id,
                            'event_type' => 'lesson_completed',
                            'title' => 'Completed Lesson: "'.$ltitle.'"',
                            'body' => null,
                            'metadata' => json_encode(['lesson_id' => $lid]),
                            'lesson_id' => $lid,
                            'quiz_id' => null,
                            'assignment_id' => null,
                            'occurred_at' => $occ,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    $quizzes = DB::table('quiz_submissions')
                        ->where('enrollment_id', $enrollment->id)
                        ->whereIn('status', ['graded', 'submitted'])
                        ->get();

                    foreach ($quizzes as $qs) {
                        $qid = (int) $qs->quiz_id;
                        $passing = (float) ($quizPassing[$qid] ?? 75);
                        $final = (float) ($qs->final_score ?? $qs->score ?? 0);
                        if ($final < $passing) {
                            continue;
                        }
                        $qtitle = $quizTitles[$qid] ?? 'Quiz';
                        $occ = $qs->submitted_at ?? $qs->created_at ?? $enrolledAt;
                        $events[] = [
                            'enrollment_id' => $enrollment->id,
                            'user_id' => $enrollment->user_id,
                            'course_id' => $enrollment->course_id,
                            'event_type' => 'quiz_passed',
                            'title' => 'Passed Quiz: "'.$qtitle.'" (Score: '.(int) $final.')',
                            'body' => null,
                            'metadata' => json_encode(['quiz_id' => $qid, 'score' => $final]),
                            'lesson_id' => null,
                            'quiz_id' => $qid,
                            'assignment_id' => null,
                            'occurred_at' => $occ,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    $submissions = DB::table('submissions')
                        ->where('enrollment_id', $enrollment->id)
                        ->whereIn('status', ['submitted', 'graded'])
                        ->orderBy('submitted_at')
                        ->get();

                    foreach ($submissions as $sub) {
                        $aid = (int) $sub->assignment_id;
                        $atitle = $assignmentTitles[$aid] ?? 'Assignment';
                        $occ = $sub->submitted_at ?? $sub->created_at ?? $enrolledAt;
                        $events[] = [
                            'enrollment_id' => $enrollment->id,
                            'user_id' => $enrollment->user_id,
                            'course_id' => $enrollment->course_id,
                            'event_type' => 'assignment_submitted',
                            'title' => 'Submitted Assignment: "'.$atitle.'"',
                            'body' => null,
                            'metadata' => json_encode([
                                'assignment_id' => $aid,
                                'submission_id' => $sub->id,
                                'status' => $sub->status,
                            ]),
                            'lesson_id' => null,
                            'quiz_id' => null,
                            'assignment_id' => $aid,
                            'occurred_at' => $occ,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        if ($sub->status === 'graded' && $sub->score !== null) {
                            $gradedAt = Carbon::parse((string) $occ)->addHour()->toDateTimeString();
                            $events[] = [
                                'enrollment_id' => $enrollment->id,
                                'user_id' => $enrollment->user_id,
                                'course_id' => $enrollment->course_id,
                                'event_type' => 'assignment_graded',
                                'title' => 'Assignment graded: "'.$atitle.'" (Score: '.(int) $sub->score.')',
                                'body' => null,
                                'metadata' => json_encode([
                                    'assignment_id' => $aid,
                                    'submission_id' => $sub->id,
                                    'score' => $sub->score,
                                ]),
                                'lesson_id' => null,
                                'quiz_id' => null,
                                'assignment_id' => $aid,
                                'occurred_at' => $gradedAt,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }

                    usort($events, function (array $a, array $b) {
                        return strcmp((string) $a['occurred_at'], (string) $b['occurred_at']);
                    });

                    foreach ($events as $row) {
                        $insertBatch[] = $row;
                        if (count($insertBatch) >= $batchSize) {
                            DB::table('enrollment_activities')->insert($insertBatch);
                            $insertBatch = [];
                        }
                    }
                }
            });

        if (! empty($insertBatch)) {
            DB::table('enrollment_activities')->insert($insertBatch);
        }

        $count = DB::table('enrollment_activities')->count();
        $this->command->info("Enrollment activities seeded: {$count} rows.");
    }
}
