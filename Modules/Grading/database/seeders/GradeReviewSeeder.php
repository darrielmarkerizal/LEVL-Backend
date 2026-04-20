<?php

namespace Modules\Grading\Database\Seeders;

use App\Support\RealisticSeederContent;
use Illuminate\Database\Seeder;

class GradeReviewSeeder extends Seeder
{
    
    public function run(): void
    {
        echo "\n📋 Seeding grade reviews...\n";

        
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

        $totalGrades = \DB::table('grades')
            ->whereIn('status', ['graded', 'reviewed'])
            ->whereNotNull('released_at')
            ->count();

        if ($totalGrades === 0) {
            echo "⚠️  No released grades found. Skipping grade review seeding.\n";

            return;
        }

        echo "   📝 Processing $totalGrades released grades...\n";
        echo '   👥 Using '.count($instructorIds)." instructors\n\n";

        $reviewCount = 0;
        $chunkNum = 0;
        $chunkSize = 2000;
        $offset = 0;

        while (true) {
            $grades = \DB::table('grades')
                ->select('id', 'user_id')
                ->whereIn('status', ['graded', 'reviewed'])
                ->whereNotNull('released_at')
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

                
                if (rand(1, 100) > 15) {
                    continue;
                }

                
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
                    'reason' => RealisticSeederContent::gradingReviewReason($grade->id),
                    'response' => in_array($reviewStatus, ['approved', 'rejected'], true)
                        ? RealisticSeederContent::gradingReviewResponse($grade->id)
                        : null,
                    'reviewed_by' => $reviewerId,
                    'status' => $reviewStatus,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $reviewCount++;
            }

            
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
