<?php

namespace Modules\Grading\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Grading\Models\Grade;

class GradeReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates comprehensive grade review data:
     * - Reviews for grades with different statuses
     * - Different review statuses (pending, approved, rejected)
     * - Random assignment of reviewers
     */
    public function run(): void
    {
        echo "\n📋 Seeding grade reviews...\n";

        // ✅ Get instructors using raw SQL
        $instructorIds = \DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['Instructor', 'Admin'])
            ->distinct()
            ->pluck('users.id')
            ->toArray();

        if (empty($instructorIds)) {
            echo "⚠️  No instructors found. Skipping grade review seeding.\n";

            return;
        }

        // Count grades using raw SQL
        $totalGrades = \DB::table('grades')->count();

        if ($totalGrades === 0) {
            echo "⚠️  No grades found. Skipping grade review seeding.\n";

            return;
        }

        echo "   📝 Processing $totalGrades grades...\n";
        echo '   👥 Using '.count($instructorIds)." instructors\n\n";

        $reviewCount = 0;
        $chunkNum = 0;
        $chunkSize = 2000;
        $offset = 0;

        // ✅ Use raw SQL for better performance
        while (true) {
            $grades = \DB::table('grades')
                ->select('id', 'user_id')
                ->limit($chunkSize)
                ->offset($offset)
                ->orderBy('id')
                ->get();

            if ($grades->isEmpty()) {
                break;
            }

            $chunkNum++;
            $reviews = [];
            $chunkGrades = 0;

            foreach ($grades as $grade) {
                $chunkGrades++;

                // ✅ 10-15% of grades get reviewed
                if (rand(1, 100) > 15) {
                    continue;
                }

                // Determine review status randomly
                $statusRandom = rand(1, 100);
                $reviewStatus = match (true) {
                    $statusRandom <= 50 => 'pending',
                    $statusRandom <= 80 => 'approved',
                    default => 'rejected',
                };

                $reviewerId = in_array($reviewStatus, ['approved', 'rejected'])
                    ? $instructorIds[array_rand($instructorIds)]
                    : null;

                $reviews[] = [
                    'grade_id' => $grade->id,
                    'requested_by' => $grade->user_id,
                    'reason' => fake()->paragraph(),
                    'response' => in_array($reviewStatus, ['approved', 'rejected']) ? fake()->paragraph() : null,
                    'reviewed_by' => $reviewerId,
                    'status' => $reviewStatus,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $reviewCount++;
            }

            // ✅ Batch insert reviews
            if (! empty($reviews)) {
                \DB::table('grade_reviews')->insertOrIgnore($reviews);
            }

            echo "      ✓ Chunk $chunkNum: $chunkGrades grades processed | Created Reviews: ".count($reviews)."\n";

            $offset += $chunkSize;
        }
        echo "\n✅ Grade review seeding completed!\n";
        echo "   📊 Total grade reviews created: $reviewCount\n";
    }
}
