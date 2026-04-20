<?php

namespace Modules\Learning\Database\Seeders;

use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Modules\Learning\Models\Assignment;

class AssignmentPrerequisitesSeeder extends Seeder
{
    
    public function run(): void
    {
        \DB::connection()->disableQueryLog();

        echo "Seeding assignment prerequisites...\n";

        
        $assignmentIds = Assignment::pluck('id');

        if ($assignmentIds->count() < 2) {
            echo "⚠️  Need at least 2 assignments to create prerequisites. Skipping.\n";

            return;
        }

        $prerequisiteCount = 0;

        
        $assignmentIds = $assignmentIds->toArray();

        
        $relationshipsToInsert = [];
        $existingRelationships = [];

        
        $existing = \DB::table('assignment_prerequisites')
            ->select('assignment_id', 'prerequisite_id')
            ->get();

        foreach ($existing as $rel) {
            $existingRelationships["{$rel->assignment_id}_{$rel->prerequisite_id}"] = true;
        }

        
        foreach ($assignmentIds as $assignmentId) {
            
            if (rand(1, 100) <= 70) {
                continue;
            }

            
            $otherAssignmentIds = array_filter($assignmentIds, function ($id) use ($assignmentId) {
                return $id != $assignmentId;
            });

            if (empty($otherAssignmentIds)) {
                continue;
            }

            
            $numPrerequisites = rand(1, min(2, count($otherAssignmentIds)));
            shuffle($otherAssignmentIds);
            $selectedPrerequisites = array_slice($otherAssignmentIds, 0, $numPrerequisites);

            foreach ($selectedPrerequisites as $prerequisiteId) {
                $relationshipKey = "{$assignmentId}_{$prerequisiteId}";

                
                if (! isset($existingRelationships[$relationshipKey])) {
                    $relationshipsToInsert[] = [
                        'assignment_id' => $assignmentId,
                        'prerequisite_id' => $prerequisiteId,
                        'created_at' => SeederDate::randomPastDateTimeBetween(1, 180),
                        'updated_at' => SeederDate::randomPastDateTimeBetween(1, 180),
                    ];

                    $prerequisiteCount++;
                }
            }
        }

        
        if (! empty($relationshipsToInsert)) {
            foreach (array_chunk($relationshipsToInsert, 1000) as $chunk) {
                \DB::table('assignment_prerequisites')->insertOrIgnore($chunk);
            }
        }

        echo "✅ Assignment prerequisites seeding completed!\n";
        echo "Created $prerequisiteCount prerequisite relationships\n";

        gc_collect_cycles();
        \DB::connection()->enableQueryLog();
    }
}
