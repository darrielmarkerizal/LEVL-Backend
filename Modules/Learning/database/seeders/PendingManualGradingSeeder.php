<?php

namespace Modules\Learning\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Models\Submission;

class PendingManualGradingSeeder extends Seeder
{
    
    public function run(): void
    {
        \DB::connection()->disableQueryLog();

        echo "\n📋 Seeding submissions with PendingManualGrading state...\n";

        
        $totalToUpdate = \DB::table('submissions as s')
            ->leftJoin('grades', 's.id', '=', 'grades.submission_id')
            ->where('s.state', SubmissionState::InProgress->value)
            ->whereNull('grades.id')
            ->count();

        if ($totalToUpdate === 0) {
            echo "⚠️  No submissions found for PendingManualGrading state.\n";

            return;
        }

        echo "   📝 Processing $totalToUpdate submissions...\n\n";

        
        $chunkSize = 2000;
        $offset = 0;
        $chunkNum = 0;
        $totalUpdated = 0;

        while (true) {
            
            $submissionIds = \DB::table('submissions as s')
                ->leftJoin('grades', 's.id', '=', 'grades.submission_id')
                ->where('s.state', SubmissionState::InProgress->value)
                ->whereNull('grades.id')
                ->select('s.id')
                ->limit($chunkSize)
                ->offset($offset)
                ->orderBy('s.id')
                ->pluck('id')
                ->toArray();

            if (empty($submissionIds)) {
                break;
            }

            $chunkNum++;
            $count = count($submissionIds);

            
            \DB::table('submissions')
                ->whereIn('id', $submissionIds)
                ->update([
                    'state' => SubmissionState::PendingManualGrading->value,
                    'status' => 'submitted',
                    'updated_at' => now(),
                ]);

            $totalUpdated += $count;

            echo "      ✓ Chunk $chunkNum: Updated $count submissions (Total: $totalUpdated/$totalToUpdate)\n";

            if ($chunkNum % 5 === 0) {
                gc_collect_cycles();
            }

            $offset += $chunkSize;
        }

        echo "\n✅ Completed! Updated $totalUpdated submissions to PendingManualGrading state\n";

        gc_collect_cycles();
        \DB::connection()->enableQueryLog();
    }
}
