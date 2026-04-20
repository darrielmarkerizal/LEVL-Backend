<?php

namespace Modules\Learning\Database\Seeders;

use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\Learning\Enums\OverrideType;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Override;

class OverrideSeeder extends Seeder
{
    
    public function run(): void
    {
        \DB::connection()->disableQueryLog();

        echo "Seeding overrides...\n";

        
        $instructorIds = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Instructor', 'Admin']);
        })->pluck('id');

        $assignmentIds = Assignment::pluck('id');
        $studentIds = User::role('Student')->pluck('id');

        if ($instructorIds->isEmpty()) {
            echo "⚠️  No instructors found. Skipping override seeding.\n";

            return;
        }

        if ($assignmentIds->isEmpty()) {
            echo "⚠️  No assignments found. Skipping override seeding.\n";

            return;
        }

        if ($studentIds->isEmpty()) {
            echo "⚠️  No students found. Skipping override seeding.\n";

            return;
        }

        $overrideCount = 0;

        
        $instructorIds = $instructorIds->toArray();
        $assignmentIds = $assignmentIds->toArray();
        $studentIds = $studentIds->toArray();

        
        $overridesToInsert = [];

        
        $assignmentsForOverride = array_slice($assignmentIds, 0, min(20, count($assignmentIds)));

        
        foreach ($assignmentsForOverride as $assignmentId) {
            
            $numOverrides = rand(1, 3);

            for ($i = 0; $i < $numOverrides; $i++) {
                $studentId = $studentIds[array_rand($studentIds)];
                $instructorId = $instructorIds[array_rand($instructorIds)];

                
                $overrideTypes = [
                    OverrideType::Deadline,
                    OverrideType::Attempts,
                    OverrideType::Prerequisite,
                ];

                $type = $overrideTypes[array_rand($overrideTypes)];

                
                $overrideData = [
                    'assignment_id' => $assignmentId,
                    'student_id' => $studentId,
                    'grantor_id' => $instructorId,
                    'type' => $type,
                    'reason' => fake()->sentence(),
                    'granted_at' => SeederDate::randomPastDateTimeBetween(30, 180),
                    'expires_at' => null, 
                    'created_at' => SeederDate::randomPastDateTimeBetween(30, 180),
                    'updated_at' => SeederDate::randomPastDateTimeBetween(30, 180),
                ];

                
                switch ($type) {
                    case OverrideType::Deadline:
                        $overrideGrantedAt = SeederDate::randomPastCarbonBetween(30, 150);
                        $overrideData['value'] = json_encode([
                            'extended_deadline' => $overrideGrantedAt->copy()->addDays(rand(1, 14))->toISOString(),
                        ], JSON_UNESCAPED_SLASHES);
                        $overrideData['granted_at'] = $overrideGrantedAt->toDateTimeString();
                        $overrideData['expires_at'] = $overrideGrantedAt->copy()->addDays(rand(15, 30))->toDateTimeString();
                        break;

                    case OverrideType::Attempts:
                        $overrideGrantedAt = SeederDate::randomPastCarbonBetween(30, 150);
                        $overrideData['value'] = json_encode([
                            'additional_attempts' => rand(1, 3),
                        ], JSON_UNESCAPED_SLASHES);
                        $overrideData['granted_at'] = $overrideGrantedAt->toDateTimeString();
                        $overrideData['expires_at'] = $overrideGrantedAt->copy()->addDays(rand(30, 60))->toDateTimeString();
                        break;

                    case OverrideType::Prerequisite:
                        $overrideGrantedAt = SeederDate::randomPastCarbonBetween(30, 150);
                        $overrideData['value'] = json_encode([
                            'bypassed_prerequisites' => [], 
                        ], JSON_UNESCAPED_SLASHES);
                        $overrideData['granted_at'] = $overrideGrantedAt->toDateTimeString();
                        $overrideData['expires_at'] = $overrideGrantedAt->copy()->addDays(rand(7, 21))->toDateTimeString();
                        break;
                }

                $overridesToInsert[] = $overrideData;
                $overrideCount++;
            }
        }

        
        if (! empty($overridesToInsert)) {
            foreach (array_chunk($overridesToInsert, 1000) as $chunk) {
                \DB::table('overrides')->insertOrIgnore($chunk);
            }
        }

        echo "✅ Override seeding completed!\n";
        echo "Created $overrideCount overrides\n";

        gc_collect_cycles();
        \DB::connection()->enableQueryLog();
    }
}
