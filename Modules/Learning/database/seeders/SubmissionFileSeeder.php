<?php

namespace Modules\Learning\Database\Seeders;

use App\Support\UATMediaFixtures;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Learning\Models\SubmissionFile;

class SubmissionFileSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection()->disableQueryLog();

        // Ensure UAT fixture files exist
        UATMediaFixtures::ensureFilesExist();

        $this->command->info('Seeding submission files with Object Storage uploads...');

        $fileUploadAssignmentIds = DB::table('assignment_questions')
            ->where('type', 'file_upload')
            ->pluck('assignment_id')
            ->unique()
            ->toArray();

        if (empty($fileUploadAssignmentIds)) {
            $this->command->warn('⚠️  No assignments with file upload questions found.');

            return;
        }

        $this->command->info('   📁 Found '.count($fileUploadAssignmentIds).' assignments');

        // Get submissions that need files
        $submissions = DB::table('submissions')
            ->whereIn('assignment_id', $fileUploadAssignmentIds)
            ->orderBy('id')
            ->get(); // We use get() here to iterate easier, assuming not millions suitable for cursor yet for complex logic

        if ($submissions->isEmpty()) {
            $this->command->warn('⚠️  No submissions found.');

            return;
        }

        $totalSubmissions = $submissions->count();
        $this->command->info("   📋 Processing $totalSubmissions submissions...");

        $fileCount = 0;
        $processed = 0;
        $bar = $this->command->getOutput()->createProgressBar($totalSubmissions);
        $bar->start();

        foreach ($submissions as $submission) {
            // Randomly decide if this submission has files (simulate some empty or failed ones? nah, lets give most files)
            // User requested robust seeding, let's say 80% have files
            if (rand(1, 100) > 80) {
                $processed++;
                $bar->advance();

                continue;
            }

            $numFiles = rand(1, 2); // 1 or 2 files per submission

            for ($i = 0; $i < $numFiles; $i++) {
                try {
                    $submissionFile = SubmissionFile::create([
                        'submission_id' => $submission->id,
                    ]);

                    // Use UAT fixture files instead of creating dummy files
                    $fileTypes = ['pdf', 'doc', 'excel'];
                    $fileType = $fileTypes[$i % count($fileTypes)];
                    $fixturePaths = UATMediaFixtures::paths();
                    $filePath = $fixturePaths[$fileType];

                    if (!file_exists($filePath)) {
                        $this->command->warn("\nFixture file not found: {$filePath}");
                        continue;
                    }

                    $fileName = "submission_{$submission->id}_file_{$i}." . pathinfo($filePath, PATHINFO_EXTENSION);

                    // Upload to Media Library (Object Storage)
                    $submissionFile->addMedia($filePath)
                        ->preservingOriginal()
                        ->usingFileName($fileName)
                        ->toMediaCollection('file');

                    $fileCount++;

                } catch (\Exception $e) {
                    $this->command->error("\nFailed to upload file for submission {$submission->id}: ".$e->getMessage());
                }
            }

            $processed++;
            $bar->advance();

            // Clean memory occasionally
            if ($processed % 100 === 0) {
                gc_collect_cycles();
            }
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ Created and uploaded $fileCount submission files to Object Storage.");

        DB::connection()->enableQueryLog();
    }
}
