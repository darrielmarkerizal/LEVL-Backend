<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class DiagnoseStorageBucket extends Command
{
    protected $signature = 'storage:diagnose 
                          {--bucket= : Specific bucket to check (default: from config)}
                          {--test-upload : Test file upload}';

    protected $description = 'Diagnose storage bucket configuration and connectivity';

    public function handle()
    {
        $this->info('=== Storage Bucket Diagnostics ===');
        $this->newLine();

        
        $this->checkEnvironmentConfig();
        $this->newLine();

        
        $this->checkDatabaseMedia();
        $this->newLine();

        
        $this->testBucketConnectivity();
        $this->newLine();

        
        $this->checkSampleMediaUrls();
        $this->newLine();

        
        if ($this->option('test-upload')) {
            $this->testFileUpload();
            $this->newLine();
        }

        $this->info('=== Diagnostics Complete ===');
    }

    protected function checkEnvironmentConfig()
    {
        $this->info('📋 Environment Configuration:');
        $this->line('─────────────────────────────');

        $configs = [
            'FILESYSTEM_DISK' => env('FILESYSTEM_DISK'),
            'DO_BUCKET' => env('DO_BUCKET'),
            'DO_DEFAULT_REGION' => env('DO_DEFAULT_REGION'),
            'DO_ENDPOINT' => env('DO_ENDPOINT'),
            'DO_CDN_URL' => env('DO_CDN_URL'),
            'DO_USE_CDN' => env('DO_USE_CDN') ? 'true' : 'false',
            'MEDIA_DISK' => env('MEDIA_DISK'),
        ];

        foreach ($configs as $key => $value) {
            $status = $value ? '✓' : '✗';
            $color = $value ? 'green' : 'red';
            $this->line("<fg={$color}>{$status}</> {$key}: " . ($value ?: '<not set>'));
        }

        
        $hasAccessKey = !empty(env('DO_ACCESS_KEY_ID'));
        $hasSecretKey = !empty(env('DO_SECRET_ACCESS_KEY'));
        
        $this->line($hasAccessKey ? '<fg=green>✓</> DO_ACCESS_KEY_ID: <configured>' : '<fg=red>✗</> DO_ACCESS_KEY_ID: <not set>');
        $this->line($hasSecretKey ? '<fg=green>✓</> DO_SECRET_ACCESS_KEY: <configured>' : '<fg=red>✗</> DO_SECRET_ACCESS_KEY: <not set>');
    }

    protected function checkDatabaseMedia()
    {
        $this->info('💾 Database Media Records:');
        $this->line('─────────────────────────────');

        try {
            
            $totalMedia = DB::table('media')->count();
            $this->line("Total media records: {$totalMedia}");

            
            $byDisk = DB::table('media')
                ->select('disk', DB::raw('count(*) as count'))
                ->groupBy('disk')
                ->get();

            $this->line("\nMedia by disk:");
            foreach ($byDisk as $disk) {
                $this->line("  - {$disk->disk}: {$disk->count} files");
            }

            
            $buckets = ['levl-assets', 'prep-lsp'];
            $this->line("\nBucket references in custom_properties:");
            
            foreach ($buckets as $bucket) {
                $count = DB::table('media')
                    ->where(DB::raw('custom_properties::text'), 'like', "%{$bucket}%")
                    ->count();
                
                if ($count > 0) {
                    $this->line("  - {$bucket}: {$count} references");
                }
            }

            
            $samples = DB::table('media')
                ->select('id', 'model_type', 'collection_name', 'file_name', 'disk')
                ->limit(3)
                ->get();

            if ($samples->isNotEmpty()) {
                $this->line("\nSample media records:");
                foreach ($samples as $sample) {
                    $this->line("  ID: {$sample->id} | {$sample->model_type} | {$sample->collection_name} | {$sample->file_name}");
                }
            }

        } catch (\Exception $e) {
            $this->error("Error checking database: " . $e->getMessage());
        }
    }

    protected function testBucketConnectivity()
    {
        $this->info('🔌 Bucket Connectivity Test:');
        $this->line('─────────────────────────────');

        $disk = env('FILESYSTEM_DISK', 'do');
        $bucket = env('DO_BUCKET');
        $endpoint = env('DO_ENDPOINT');
        $cdnUrl = env('DO_CDN_URL');

        try {
            
            $this->line("Testing disk: {$disk}");
            
            if (!config("filesystems.disks.{$disk}")) {
                $this->error("✗ Disk '{$disk}' not configured in filesystems.php");
                return;
            }
            $this->line("<fg=green>✓</> Disk configuration found");

            
            $this->line("\nTesting bucket access...");
            try {
                $files = Storage::disk($disk)->files('/', false);
                $this->line("<fg=green>✓</> Successfully connected to bucket");
                $this->line("  Found " . count($files) . " files in root directory");
            } catch (\Exception $e) {
                $this->error("✗ Cannot access bucket: " . $e->getMessage());
                $this->line("  This usually means:");
                $this->line("  - Invalid credentials");
                $this->line("  - Bucket doesn't exist");
                $this->line("  - Wrong region/endpoint");
            }

            
            if ($cdnUrl) {
                $this->line("\nTesting CDN URL accessibility...");
                try {
                    $response = Http::timeout(5)->head($cdnUrl);
                    if ($response->successful() || $response->status() === 403) {
                        $this->line("<fg=green>✓</> CDN URL is accessible");
                    } else {
                        $this->warn("⚠ CDN URL returned status: " . $response->status());
                    }
                } catch (\Exception $e) {
                    $this->error("✗ Cannot reach CDN URL: " . $e->getMessage());
                }
            }

            
            $this->line("\nChecking alternative buckets...");
            $bucketsToCheck = ['levl-assets', 'prep-lsp'];
            
            foreach ($bucketsToCheck as $testBucket) {
                if ($testBucket === $bucket) {
                    continue; 
                }
                
                $testUrl = str_replace($bucket, $testBucket, $cdnUrl ?: $endpoint);
                try {
                    $response = Http::timeout(5)->head($testUrl);
                    if ($response->successful() || $response->status() === 403) {
                        $this->line("<fg=yellow>!</> Bucket '{$testBucket}' exists and is accessible");
                        $this->line("    URL: {$testUrl}");
                    }
                } catch (\Exception $e) {
                    $this->line("<fg=gray>-</> Bucket '{$testBucket}' not accessible or doesn't exist");
                }
            }

        } catch (\Exception $e) {
            $this->error("Error during connectivity test: " . $e->getMessage());
        }
    }

    protected function checkSampleMediaUrls()
    {
        $this->info('🔗 Sample Media URLs Test:');
        $this->line('─────────────────────────────');

        try {
            $samples = DB::table('media')
                ->select('id', 'file_name', 'disk')
                ->limit(5)
                ->get();

            if ($samples->isEmpty()) {
                $this->line("No media records found in database");
                return;
            }

            foreach ($samples as $sample) {
                try {
                    $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($sample->id);
                    if ($media) {
                        $url = $media->getUrl();
                        $this->line("\nFile: {$sample->file_name}");
                        $this->line("URL: {$url}");
                        
                        
                        try {
                            $response = Http::timeout(5)->head($url);
                            if ($response->successful()) {
                                $this->line("<fg=green>✓</> Accessible (Status: {$response->status()})");
                            } else {
                                $this->error("✗ Not accessible (Status: {$response->status()})");
                            }
                        } catch (\Exception $e) {
                            $this->error("✗ Cannot reach URL: " . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("Error checking media ID {$sample->id}: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            $this->error("Error checking sample URLs: " . $e->getMessage());
        }
    }

    protected function testFileUpload()
    {
        $this->info('📤 Test File Upload:');
        $this->line('─────────────────────────────');

        try {
            $disk = env('FILESYSTEM_DISK', 'do');
            $testFileName = 'test-' . time() . '.txt';
            $testContent = 'This is a test file created by storage:diagnose command at ' . now();

            $this->line("Attempting to upload test file: {$testFileName}");
            
            Storage::disk($disk)->put($testFileName, $testContent);
            $this->line("<fg=green>✓</> File uploaded successfully");

            
            $content = Storage::disk($disk)->get($testFileName);
            if ($content === $testContent) {
                $this->line("<fg=green>✓</> File content verified");
            } else {
                $this->warn("⚠ File content mismatch");
            }

            
            $url = Storage::disk($disk)->url($testFileName);
            $this->line("File URL: {$url}");

            
            try {
                $response = Http::timeout(5)->get($url);
                if ($response->successful()) {
                    $this->line("<fg=green>✓</> File accessible via URL");
                } else {
                    $this->warn("⚠ File not accessible via URL (Status: {$response->status()})");
                }
            } catch (\Exception $e) {
                $this->error("✗ Cannot access file via URL: " . $e->getMessage());
            }

            
            if ($this->confirm('Delete test file?', true)) {
                Storage::disk($disk)->delete($testFileName);
                $this->line("<fg=green>✓</> Test file deleted");
            }

        } catch (\Exception $e) {
            $this->error("Upload test failed: " . $e->getMessage());
        }
    }
}
