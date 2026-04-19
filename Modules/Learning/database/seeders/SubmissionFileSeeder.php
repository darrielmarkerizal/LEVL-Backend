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

        
        $submissions = DB::table('submissions')
            ->whereIn('assignment_id', $fileUploadAssignmentIds)
            ->orderBy('id')
            ->get(); 

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
            
            
            if (rand(1, 100) > 80) {
                $processed++;
                $bar->advance();

                continue;
            }

            $numFiles = rand(1, 2); 

            for ($i = 0; $i < $numFiles; $i++) {
                try {
                    $submissionFile = SubmissionFile::create([
                        'submission_id' => $submission->id,
                    ]);

                    
                    $fileTypes = ['pdf', 'doc', 'excel'];
                    $fileType = $fileTypes[$i % count($fileTypes)];
                    $fixturePaths = UATMediaFixtures::paths();
                    $filePath = $fixturePaths[$fileType];

                    if (!file_exists($filePath)) {
                        $this->command->warn("\nFixture file not found: {$filePath}");
                        continue;
                    }

                    $fileName = "submission_{$submission->id}_file_{$i}." . pathinfo($filePath, PATHINFO_EXTENSION);

                    
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
