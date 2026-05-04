<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('auth:cleanup-deleted-accounts')->daily();


Schedule::command('trash:purge-expired')->daily();


Schedule::command('posts:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();


Schedule::command('enrollments:activate-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();


Schedule::command('posts:cleanup-orphaned-media')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
