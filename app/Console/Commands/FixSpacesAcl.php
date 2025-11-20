<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

class FixSpacesAcl extends Command
{
    protected $signature = 'spaces:fix-acl {directory?} {--all}';
    protected $description = 'Fix ACL for files in DigitalOcean Spaces to make them publicly accessible';

    public function handle()
    {
        $directory = $this->argument('directory');
        $all = $this->option('all');

        if ($all) {
            $this->info('Fixing ACL for ALL files in the bucket...');
            $directories = ['courses/thumbnails', 'courses/banners', 'lessons/media'];
        } elseif ($directory) {
            $directories = [$directory];
        } else {
            $directories = ['courses/thumbnails', 'courses/banners'];
        }

        $disk = Storage::disk('do');
        $config = config('filesystems.disks.do');

        if (!isset($config['key']) || !isset($config['secret'])) {
            $this->error('DO Spaces credentials not configured!');
            $this->info('Please add DO_ACCESS_KEY_ID and DO_SECRET_ACCESS_KEY to .env');
            return 1;
        }

        // Create S3 client
        $client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
            'endpoint' => $config['endpoint'],
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
        ]);

        $bucket = $config['bucket'];
        $totalFixed = 0;

        foreach ($directories as $dir) {
            $this->info("\nProcessing directory: {$dir}");
            $this->newLine();

            try {
                $files = $disk->allFiles($dir);

                if (empty($files)) {
                    $this->warn("  No files found in {$dir}");
                    continue;
                }

                $bar = $this->output->createProgressBar(count($files));
                $bar->start();

                foreach ($files as $file) {
                    try {
                        // Set ACL to public-read
                        $client->putObjectAcl([
                            'Bucket' => $bucket,
                            'Key' => $file,
                            'ACL' => 'public-read',
                        ]);

                        $totalFixed++;
                        $bar->advance();
                    } catch (\Exception $e) {
                        $this->newLine();
                        $this->error("  Failed to fix ACL for: {$file}");
                        $this->error("  Error: " . $e->getMessage());
                        $bar->advance();
                    }
                }

                $bar->finish();
                $this->newLine();
                $this->info("  ✓ Fixed " . count($files) . " files in {$dir}");
            } catch (\Exception $e) {
                $this->error("  Error processing directory {$dir}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("✓ Total files fixed: {$totalFixed}");
        $this->newLine();
        $this->info('Next steps:');
        $this->info('1. Clear config cache: php artisan config:clear');
        $this->info('2. Test file URLs in browser');
        $this->info('3. Try uploading new files');

        return 0;
    }
}
