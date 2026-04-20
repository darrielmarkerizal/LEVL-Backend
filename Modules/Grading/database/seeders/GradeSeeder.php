<?php

namespace Modules\Grading\Database\Seeders;

use App\Support\SeederDate;
use App\Support\RealisticSeederContent;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    
    public function run(): void
    {
        \DB::connection()->disableQueryLog();
        ini_set('memory_limit', '1536M');

        echo "\n📋 Seeding grades...\n";

        $pregenFeedback = [];
        $createdAt = SeederDate::randomPastDateTimeBetween(7, 180);

        for ($i = 0; $i < 100; $i++) {
            $pregenFeedback[] = RealisticSeederContent::assignmentFeedback($i);
        }

        $instructorIds = \DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['Instructor', 'Admin'])
            ->distinct()
            ->pluck('users.id')
            ->toArray();

        if (empty($instructorIds)) {
            echo "⚠️  No instructors found. Skipping grading seeding.\n";

            return;
        }

        $totalSubmissions = \DB::table('submissions')
            ->whereIn('status', ['submitted', 'graded'])
            ->count();

        if ($totalSubmissions === 0) {
            echo "⚠️  No submissions found. Skipping grading seeding.\n";

            return;
        }

        echo "   📝 Processing $totalSubmissions submissions...\n";
        echo '   👥 Using '.count($instructorIds)." instructors\n\n";

        $gradeCount = 0;

        $chunkNum = 0;
        $chunkSize = 1000;
        $offset = 0;

        while (true) {
            $submissions = \DB::table('submissions')
                ->select('submissions.id', 'submissions.user_id', 'submissions.assignment_id')
                ->join('assignments', 'submissions.assignment_id', '=', 'assignments.id')
                ->whereIn('submissions.status', ['submitted', 'graded'])
                ->select('submissions.id', 'submissions.user_id', 'submissions.assignment_id', 'assignments.max_score')
                ->limit($chunkSize)
                ->offset($offset)
                ->orderBy('submissions.id')
                ->get();

            if ($submissions->isEmpty()) {
                break;
            }

            $chunkNum++;
            $grades = [];
            $chunkSubmissions = 0;

            foreach ($submissions as $submission) {
                $chunkSubmissions++;

                if (rand(1, 100) > 70) {
                    continue;
                }

                $instructorId = $instructorIds[array_rand($instructorIds)];

                $statusRandom = rand(1, 100);
                $gradeStatus = match (true) {
                    $statusRandom <= 60 => 'graded',
                    $statusRandom <= 80 => 'pending',
                    default => 'reviewed',
                };

                $gradedAt = SeederDate::randomPastCarbonBetween(7, 170);
                $releasedAt = $gradeStatus === 'pending'
                    ? null
                    : $gradedAt->copy()->addDays(rand(0, 7))->toDateTimeString();

                $grades[] = [
                    'source_id' => $submission->assignment_id,
                    'source_type' => 'assignment',
                    'user_id' => $submission->user_id,
                    'submission_id' => $submission->id,
                    'graded_by' => $instructorId,
                    'score' => $gradeStatus === 'pending' ? null : rand(0, $submission->max_score),
                    'max_score' => $submission->max_score,
                    'feedback' => $gradeStatus === 'pending' ? null : $pregenFeedback[array_rand($pregenFeedback)],
                    'status' => $gradeStatus,
                    'graded_at' => $gradeStatus === 'pending' ? null : $gradedAt->toDateTimeString(),
                    'released_at' => $releasedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
                $gradeCount++;
            }

            if (! empty($grades)) {
                \DB::table('grades')->insertOrIgnore($grades);
            }

            unset($grades);

            echo "      ✓ Chunk $chunkNum: $chunkSubmissions submissions | Grades: $gradeCount\n";

            if ($chunkNum % 3 === 0) {
                gc_collect_cycles();
            }

            $offset += $chunkSize;
        }

        unset($pregenFeedback);

        echo "\n✅ Grading seeding completed!\n";
        echo "   📊 Total grades created: $gradeCount\n";
        echo "   📊 Total grades created: $gradeCount\n";

        gc_collect_cycles();
        \DB::connection()->enableQueryLog();
    }
}
