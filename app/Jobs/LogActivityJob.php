<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $activityData
    ) {
        $this->onQueue('logging');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        activity($this->activityData['log_name'] ?? 'default')
            ->causedBy($this->activityData['causer_id'] ?? null)
            ->performedOn($this->activityData['subject'] ?? null)
            ->withProperties($this->activityData['properties'] ?? [])
            ->tap(function (\Illuminate\Database\Eloquent\Model $activity) {
                if (! isset($this->activityData['device_info']) || ! is_array($this->activityData['device_info'])) {
                    return;
                }

                if ($activity instanceof \App\Models\ActivityLog) {
                    $nav = $this->activityData['device_info'];
                    $activity->ip_address = $nav['ip_address'] ?? null;
                    $activity->browser = $nav['browser'] ?? null;
                    $activity->browser_version = $nav['browser_version'] ?? null;
                    $activity->platform = $nav['platform'] ?? null;
                    $activity->device = $nav['device'] ?? null;
                    $activity->device_type = $nav['device_type'] ?? null;
                    $activity->city = $nav['city'] ?? null;
                    $activity->region = $nav['region'] ?? null;
                    $activity->country = $nav['country'] ?? null;
                }
            })
            ->log($this->activityData['description'] ?? '');
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to log activity', [
            'data' => $this->activityData,
            'error' => $exception->getMessage(),
        ]);
    }
}
