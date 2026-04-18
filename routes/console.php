<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Content Publishing
Schedule::job(new \Modules\Content\Jobs\PublishScheduledContent)->everyFiveMinutes();

// Schedule Account Cleanup (Daily)
Schedule::command('auth:cleanup-deleted-accounts')->daily();

// Schedule Trash Bin Purge (Daily)
Schedule::command('trash:purge-expired')->daily();

// Housekeeping: mark missing submissions shortly after deadlines
Schedule::job(new \Modules\Learning\Jobs\MarkMissingSubmissionsJob)->everyMinute();

// Schedule Post Publishing (Every Minute)
Schedule::command('posts:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule Assignment Publishing (Every Minute)
Schedule::command('assignments:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule Enrollment Activation (Every Minute)
Schedule::command('enrollments:activate-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule Orphaned Media Cleanup (Daily at 2 AM)
Schedule::command('posts:cleanup-orphaned-media')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
