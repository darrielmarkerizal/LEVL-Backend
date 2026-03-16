# Trash Queue Monitoring Guide

**Target:** Monitoring trash operations yang berjalan di background queue  
**Queue Name:** `trash`  
**Updated:** 17 Maret 2026

---

## 1. Quick Monitoring Commands

### A. Check Trash Queue Status
```bash
# Check pending jobs di trash queue
redis-cli LLEN queues:trash

# Check semua queue sekaligus
redis-cli LLEN queues:trash && \
redis-cli LLEN queues:emails-critical && \
redis-cli LLEN queues:emails-transactional

# Real-time monitoring (update setiap 1 detik)
watch -n 1 'redis-cli LLEN queues:trash'
```

### B. Check Queue Workers
```bash
# Check apakah trash worker berjalan
ps aux | grep "queue:work.*trash"

# Check semua queue workers
ps aux | grep "queue:work"

# Dengan supervisor
sudo supervisorctl status | grep trash
```

### C. Check Failed Jobs
```bash
# List semua failed jobs
php artisan queue:failed

# Filter hanya trash jobs
php artisan queue:failed | grep -i "trash"

# Count failed trash jobs
php artisan queue:failed | grep -i "trash" | wc -l
```

---

## 2. Laravel Artisan Commands

### A. Queue Monitoring
```bash
# Monitor trash queue dengan threshold
php artisan queue:monitor redis:trash --max=50

# Jika queue depth > 50, akan trigger alert
# Output: Queue [redis:trash] has 75 jobs pending.
```

### B. Queue Statistics
```bash
# Lihat queue statistics (requires Horizon)
php artisan horizon:stats

# Lihat recent jobs
php artisan horizon:list recent

# Lihat failed jobs
php artisan horizon:list failed
```

### C. Failed Job Management
```bash
# Retry specific failed job
php artisan queue:retry {job-id}

# Retry semua trash jobs yang failed
php artisan queue:failed | grep -i "trash" | awk '{print $2}' | xargs -I {} php artisan queue:retry {}

# Retry all failed jobs
php artisan queue:retry all

# Delete failed job
php artisan queue:forget {job-id}

# Flush all failed jobs
php artisan queue:flush
```

---

## 3. Redis Monitoring

### A. Queue Depth Monitoring
```bash
# Check queue depth
redis-cli LLEN queues:trash

# Check reserved jobs (jobs being processed)
redis-cli LLEN queues:trash:reserved

# Check delayed jobs
redis-cli ZCARD queues:trash:delayed

# Check all trash-related keys
redis-cli KEYS "*trash*"
```

### B. Job Details
```bash
# Peek at next job in queue (without removing)
redis-cli LINDEX queues:trash 0

# Get job details (pretty print)
redis-cli LINDEX queues:trash 0 | jq .

# Count jobs by type
redis-cli LRANGE queues:trash 0 -1 | grep -o '"displayName":"[^"]*"' | sort | uniq -c
```

### C. Queue Performance
```bash
# Monitor Redis memory usage
redis-cli INFO memory | grep used_memory_human

# Monitor Redis operations per second
redis-cli INFO stats | grep instantaneous_ops_per_sec

# Monitor connected clients (workers)
redis-cli INFO clients | grep connected_clients
```

---

## 4. Application-Level Monitoring

### A. Custom Monitoring Command
Buat command untuk monitoring trash operations:

```php
// app/Console/Commands/MonitorTrashQueue.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Modules\Trash\Models\TrashBin;

class MonitorTrashQueue extends Command
{
    protected $signature = 'trash:monitor {--interval=5}';
    protected $description = 'Monitor trash queue operations';

    public function handle(): int
    {
        $interval = (int) $this->option('interval');

        $this->info('Monitoring trash queue (Ctrl+C to stop)...');
        $this->newLine();

        while (true) {
            $this->displayStats();
            sleep($interval);
            
            // Clear screen untuk refresh
            if (PHP_OS_FAMILY !== 'Windows') {
                system('clear');
            }
        }

        return self::SUCCESS;
    }

    private function displayStats(): void
    {
        // Queue stats
        $queueDepth = Redis::connection('queue')->llen('queues:trash');
        $reservedJobs = Redis::connection('queue')->llen('queues:trash:reserved');
        $delayedJobs = Redis::connection('queue')->zcard('queues:trash:delayed');

        // Database stats
        $totalTrashBins = TrashBin::count();
        $expiringSoon = TrashBin::where('expires_at', '<=', now()->addDays(7))->count();
        $expired = TrashBin::where('expires_at', '<=', now())->count();

        // Failed jobs
        $failedJobs = \DB::table('failed_jobs')
            ->where('queue', 'trash')
            ->count();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Queue Depth', $queueDepth],
                ['Processing', $reservedJobs],
                ['Delayed', $delayedJobs],
                ['Failed Jobs', $failedJobs],
                ['Total Trash Bins', $totalTrashBins],
                ['Expiring Soon (7d)', $expiringSoon],
                ['Expired', $expired],
            ]
        );

        $this->info('Last updated: ' . now()->format('Y-m-d H:i:s'));
    }
}
```

**Usage:**
```bash
# Monitor dengan refresh setiap 5 detik
php artisan trash:monitor

# Monitor dengan refresh setiap 2 detik
php artisan trash:monitor --interval=2
```

### B. Logging Enhancement

Tambahkan logging di trash jobs untuk better monitoring:

```php
// Modules/Trash/app/Jobs/ForceDeleteTrashBinJob.php
public function handle(TrashBinService $trashBinService): void
{
    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    $bin = TrashBin::query()->find($this->trashBinId);

    if (! $bin) {
        Log::info('ForceDeleteTrashBinJob: trash bin not found', [
            'trash_bin_id' => $this->trashBinId,
            'actor_id' => $this->actorId,
        ]);
        return;
    }

    Log::info('ForceDeleteTrashBinJob: Starting', [
        'trash_bin_id' => $this->trashBinId,
        'resource_type' => $bin->resource_type,
        'group_uuid' => $bin->group_uuid,
    ]);

    try {
        $trashBinService->forceDeleteFromTrashBin($bin);

        $duration = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;

        Log::info('ForceDeleteTrashBinJob: Completed', [
            'trash_bin_id' => $this->trashBinId,
            'duration_seconds' => round($duration, 2),
            'memory_mb' => round($memoryUsed / 1024 / 1024, 2),
        ]);
    } catch (\Throwable $e) {
        Log::error('ForceDeleteTrashBinJob: Failed', [
            'trash_bin_id' => $this->trashBinId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw $e;
    }
}
```

### C. Metrics Collection

Buat trait untuk collect metrics:

```php
// app/Jobs/Concerns/CollectsMetrics.php
<?php

namespace App\Jobs\Concerns;

use Illuminate\Support\Facades\Log;

trait CollectsMetrics
{
    protected float $startTime;
    protected int $startMemory;

    protected function startMetrics(): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }

    protected function logMetrics(string $jobName, array $context = []): void
    {
        $duration = microtime(true) - $this->startTime;
        $memoryUsed = memory_get_usage() - $this->startMemory;
        $peakMemory = memory_get_peak_usage();

        Log::info("Job Metrics: {$jobName}", array_merge($context, [
            'duration_seconds' => round($duration, 2),
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
            'queue' => $this->queue ?? 'default',
        ]));
    }
}
```

**Usage:**
```php
class ForceDeleteTrashBinJob implements ShouldQueue
{
    use CollectsMetrics;

    public function handle(TrashBinService $trashBinService): void
    {
        $this->startMetrics();

        // ... job logic ...

        $this->logMetrics('ForceDeleteTrashBinJob', [
            'trash_bin_id' => $this->trashBinId,
        ]);
    }
}
```

---

## 5. Dashboard Monitoring (Laravel Horizon)

### A. Install Horizon
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

### B. Configure Horizon
```php
// config/horizon.php
'environments' => [
    'production' => [
        'trash-worker' => [
            'connection' => 'redis',
            'queue' => ['trash'],
            'balance' => 'auto',
            'processes' => 2,
            'tries' => 3,
            'timeout' => 600,
            'memory' => 512,
        ],
        'email-worker' => [
            'connection' => 'redis',
            'queue' => ['emails-critical', 'emails-transactional'],
            'balance' => 'auto',
            'processes' => 5,
            'tries' => 3,
            'timeout' => 120,
        ],
    ],
],
```

### C. Access Horizon Dashboard
```
URL: http://your-domain.com/horizon

Features:
- Real-time queue monitoring
- Job throughput graphs
- Failed job management
- Worker status
- Job metrics
```

---

## 6. Alerting & Notifications

### A. Queue Depth Alert

```php
// app/Console/Commands/CheckTrashQueueHealth.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class CheckTrashQueueHealth extends Command
{
    protected $signature = 'trash:check-health';
    protected $description = 'Check trash queue health and alert if needed';

    private const QUEUE_DEPTH_THRESHOLD = 100;
    private const FAILED_JOBS_THRESHOLD = 10;

    public function handle(): int
    {
        $queueDepth = Redis::connection('queue')->llen('queues:trash');
        $failedJobs = \DB::table('failed_jobs')
            ->where('queue', 'trash')
            ->count();

        // Check queue depth
        if ($queueDepth > self::QUEUE_DEPTH_THRESHOLD) {
            $this->alert("⚠️ Trash queue depth is high: {$queueDepth} jobs");
            
            Log::warning('Trash queue depth threshold exceeded', [
                'queue_depth' => $queueDepth,
                'threshold' => self::QUEUE_DEPTH_THRESHOLD,
            ]);

            // Send notification to admin
            // Notification::route('mail', config('mail.admin_email'))
            //     ->notify(new QueueDepthAlert('trash', $queueDepth));
        }

        // Check failed jobs
        if ($failedJobs > self::FAILED_JOBS_THRESHOLD) {
            $this->alert("⚠️ Too many failed trash jobs: {$failedJobs}");
            
            Log::warning('Trash failed jobs threshold exceeded', [
                'failed_jobs' => $failedJobs,
                'threshold' => self::FAILED_JOBS_THRESHOLD,
            ]);
        }

        if ($queueDepth <= self::QUEUE_DEPTH_THRESHOLD && $failedJobs <= self::FAILED_JOBS_THRESHOLD) {
            $this->info('✓ Trash queue health is good');
        }

        return self::SUCCESS;
    }
}
```

**Schedule in Kernel:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Check trash queue health every 5 minutes
    $schedule->command('trash:check-health')
        ->everyFiveMinutes()
        ->withoutOverlapping();
}
```

### B. Worker Status Alert

```bash
# Cron job untuk check worker status
*/5 * * * * /usr/bin/php /var/www/levl-be/artisan queue:monitor redis:trash --max=100 || echo "Trash queue alert" | mail -s "Queue Alert" admin@example.com
```

---

## 7. Performance Monitoring

### A. Job Duration Tracking

```php
// app/Observers/JobObserver.php
<?php

namespace App\Observers;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;

class JobObserver
{
    public function processed(JobProcessed $event): void
    {
        if (str_contains($event->job->getQueue(), 'trash')) {
            $payload = $event->job->payload();
            
            Log::info('Trash job processed', [
                'job' => $payload['displayName'] ?? 'Unknown',
                'queue' => $event->job->getQueue(),
                'attempts' => $event->job->attempts(),
            ]);
        }
    }

    public function failed(JobFailed $event): void
    {
        if (str_contains($event->job->getQueue(), 'trash')) {
            Log::error('Trash job failed', [
                'job' => $event->job->resolveName(),
                'queue' => $event->job->getQueue(),
                'exception' => $event->exception->getMessage(),
            ]);
        }
    }
}
```

**Register in EventServiceProvider:**
```php
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;

public function boot(): void
{
    JobProcessed::listen(function ($event) {
        app(JobObserver::class)->processed($event);
    });

    JobFailed::listen(function ($event) {
        app(JobObserver::class)->failed($event);
    });
}
```

### B. Slow Job Detection

```php
// config/queue.php
'connections' => [
    'redis' => [
        // ...
        'after_commit' => false,
        
        // Log slow jobs
        'slow_job_threshold' => 60, // seconds
    ],
],
```

---

## 8. Troubleshooting Guide

### Problem 1: Queue Stuck (No Jobs Processing)

**Symptoms:**
- Queue depth terus naik
- Jobs tidak diproses

**Diagnosis:**
```bash
# Check worker status
ps aux | grep "queue:work.*trash"

# Check Redis connection
redis-cli ping

# Check failed jobs
php artisan queue:failed | grep trash
```

**Solutions:**
```bash
# Restart workers
sudo supervisorctl restart levl-queue-trash:*

# Or manually
php artisan queue:restart
php artisan queue:work redis --queue=trash --tries=3 --timeout=600
```

### Problem 2: Jobs Failing Repeatedly

**Symptoms:**
- Banyak failed jobs
- Same error berulang

**Diagnosis:**
```bash
# Check failed jobs detail
php artisan queue:failed

# Check logs
tail -f storage/logs/laravel.log | grep -i trash
```

**Solutions:**
```bash
# Fix the issue, then retry
php artisan queue:retry all

# Or retry specific job
php artisan queue:retry {job-id}

# If unfixable, delete failed jobs
php artisan queue:flush
```

### Problem 3: Memory Leak

**Symptoms:**
- Worker memory terus naik
- Worker crash dengan "out of memory"

**Diagnosis:**
```bash
# Monitor worker memory
watch -n 1 'ps aux | grep "queue:work.*trash" | grep -v grep'
```

**Solutions:**
```bash
# Restart worker setelah N jobs
php artisan queue:work redis --queue=trash --max-jobs=100

# Restart worker setelah N seconds
php artisan queue:work redis --queue=trash --max-time=3600

# Limit memory
php artisan queue:work redis --queue=trash --memory=512
```

### Problem 4: Timeout Issues

**Symptoms:**
- Jobs timeout sebelum selesai
- "Job has been attempted too many times"

**Diagnosis:**
```bash
# Check job timeout setting
grep -r "timeout" Modules/Trash/app/Jobs/

# Check worker timeout
ps aux | grep "queue:work.*trash"
```

**Solutions:**
```php
// Increase job timeout
public int $timeout = 600; // 10 minutes

// Or increase worker timeout
php artisan queue:work redis --queue=trash --timeout=600
```

---

## 9. Best Practices

### A. Regular Maintenance
```bash
# Daily: Check failed jobs
0 9 * * * /usr/bin/php /var/www/levl-be/artisan queue:failed | mail -s "Daily Failed Jobs Report" admin@example.com

# Weekly: Purge old failed jobs (older than 7 days)
0 0 * * 0 /usr/bin/php /var/www/levl-be/artisan queue:prune-failed --hours=168

# Monthly: Analyze queue performance
0 0 1 * * /usr/bin/php /var/www/levl-be/artisan trash:performance-report
```

### B. Monitoring Checklist
- [ ] Queue depth < 100
- [ ] Failed jobs < 10
- [ ] Worker processes running
- [ ] Memory usage < 80%
- [ ] Average job duration < 60s
- [ ] No jobs older than 10 minutes in queue

### C. Alert Thresholds
```
Queue Depth:
- Normal: 0-50
- Warning: 51-100
- Critical: > 100

Failed Jobs:
- Normal: 0-5
- Warning: 6-10
- Critical: > 10

Job Duration:
- Normal: < 60s
- Warning: 60-180s
- Critical: > 180s
```

---

## 10. Quick Reference

### Essential Commands
```bash
# Check queue
redis-cli LLEN queues:trash

# Monitor queue
php artisan queue:monitor redis:trash --max=50

# Check workers
ps aux | grep "queue:work.*trash"

# Check failed
php artisan queue:failed | grep trash

# Retry all
php artisan queue:retry all

# Restart workers
php artisan queue:restart
```

### Log Locations
```
Queue logs: storage/logs/queue-trash.log
Laravel logs: storage/logs/laravel.log
Supervisor logs: /var/log/supervisor/levl-queue-trash-*.log
```

### Important Metrics
- Queue depth: Target < 50
- Processing time: Target < 60s
- Failed rate: Target < 1%
- Worker uptime: Target 99.9%

---

**Last Updated:** 17 Maret 2026  
**Maintained By:** DevOps Team
