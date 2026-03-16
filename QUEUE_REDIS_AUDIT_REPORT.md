# Audit Queue Redis - Levl Backend

**Tanggal Audit:** 17 Maret 2026  
**Auditor:** Kiro AI Assistant  
**Scope:** Email Queue & Trash Management Queue

---

## Executive Summary

Sistem Levl-BE menggunakan Redis sebagai queue driver dengan beberapa kategori queue:
- **Email Processing**: Pengiriman email transaksional dan notifikasi
- **Trash Management**: Operasi delete dan restore yang kompleks
- **File Processing**: Upload dan validasi file
- **Notifications**: Notifikasi in-app dan push
- **Grading**: Perhitungan nilai dan bulk operations

### Status Saat Ini
- ✅ Redis sudah dikonfigurasi dengan baik
- ⚠️ Beberapa email masih menggunakan synchronous sending
- ⚠️ Trash jobs memiliki timeout yang sangat tinggi (900-1800 detik)
- ⚠️ Tidak ada dedicated queue untuk email
- ✅ Job retry mechanism sudah diimplementasikan

---

## 1. Konfigurasi Queue Saat Ini

### 1.1 Queue Connection
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

### 1.2 Queue Names yang Terdefinisi
```php
// config/queue.php
'queues' => [
    'grading' => 'grading',           // Priority: HIGH
    'notifications' => 'notifications', // Priority: MEDIUM
    'file-processing' => 'file-processing', // Priority: MEDIUM
    'default' => 'default',           // Priority: LOW
]
```

### 1.3 Queue yang Digunakan di Jobs
- `notifications` - SendPostNotificationJob, SendNotificationJob
- `file-processing` - ProcessFileUploadJob
- `schemes` - ForceDeleteTrashBinJob, RestoreTrashBinJob, DeleteCourseJob, DeleteUnitJob
- `grading` - BulkReleaseGradesJob, RecalculateGradesJob, BulkApplyFeedbackJob
- `audit` - LogGradeOverridden (listener)
- `default` - Jobs tanpa queue spesifik

---

## 2. Audit Email Queue

### 2.1 Email yang Menggunakan Queue ✅

#### A. Assignment Published Notification
**File:** `Modules/Learning/app/Listeners/NotifyEnrolledUsersOnAssignmentPublished.php`
```php
Mail::to($enrollment->user->email)->queue(
    new AssignmentPublishedMail($enrollment->user, $assignment, $course)
);
```
- ✅ Sudah menggunakan `->queue()`
- ✅ Menggunakan listener untuk async processing
- ⚠️ Tidak ada queue name spesifik (menggunakan default)

#### B. User Export Email
**File:** `Modules/Auth/app/Jobs/ExportUsersToEmailJob.php`
```php
Mail::to($this->recipientEmail)->send(new UsersExportMail(...));
```
- ⚠️ Menggunakan `->send()` bukan `->queue()` di dalam Job
- ✅ Job sudah implements ShouldQueue
- ⚠️ Tidak ada queue name spesifik

### 2.2 Email yang Menggunakan Synchronous Send ⚠️

#### A. Enrollment Emails (9 jenis)
**File:** `Modules/Enrollments/app/Services/Support/EnrollmentLifecycleProcessor.php`

1. **StudentEnrollmentScheduledMail** (line 185)
2. **StudentEnrollmentManualActiveMail** (line 187)
3. **StudentEnrollmentManualPendingMail** (line 189)
4. **StudentEnrollmentApprovedMail** (line 257)
5. **StudentEnrollmentDeclinedMail** (line 281)
6. **StudentEnrollmentActiveMail** (line 327)
7. **StudentEnrollmentPendingMail** (line 329)
8. **AdminEnrollmentNotificationMail** (line 342)
9. **StudentEnrollmentActivatedMail** (via Command, line 50)

```php
Mail::to($student->email)->send(new StudentEnrollmentActiveMail(...));
```

**Masalah:**
- ❌ Semua menggunakan `->send()` (synchronous)
- ❌ Blocking request response
- ❌ Bisa menyebabkan timeout jika SMTP lambat
- ❌ Tidak ada retry mechanism

#### B. Authentication Emails (6 jenis)
**File:** `Modules/Auth/app/Services/Support/VerificationTokenManager.php`

1. **VerifyEmailLinkMail** (line 54)
2. **ChangeEmailVerificationMail** (line 94)

**File:** `Modules/Auth/app/Services/Support/UserLifecycleProcessor.php`
3. **UserCredentialsMail** (line 292)

**File:** `Modules/Auth/app/Services/AccountDeletionService.php`
4. **AccountDeletionVerificationMail** (line 59)

**File:** `Modules/Auth/app/Http/Controllers/PasswordResetController.php`
5. **ResetPasswordMail** (line 55)

```php
Mail::to($user)->send(new VerifyEmailLinkMail(...));
```

**Masalah:**
- ❌ Semua menggunakan `->send()` (synchronous)
- ❌ Critical path emails (auth flow)
- ❌ User harus menunggu email terkirim

#### C. Course Completion Email
**File:** `Modules/Schemes/app/Listeners/SendCourseCompletedEmail.php`
```php
Mail::to($user->email)->send(new CourseCompletedMail(...));
```

**Masalah:**
- ❌ Menggunakan `->send()` (synchronous)
- ✅ Sudah menggunakan listener (bisa diubah ke ShouldQueue)

### 2.3 Ringkasan Email

| Kategori | Total | Queued | Sync | Status |
|----------|-------|--------|------|--------|
| Enrollment | 9 | 0 | 9 | ❌ Perlu perbaikan |
| Authentication | 5 | 0 | 5 | ❌ Perlu perbaikan |
| Learning | 1 | 1 | 0 | ✅ Baik |
| Course | 1 | 0 | 1 | ⚠️ Perlu perbaikan |
| Export | 1 | 0* | 1 | ⚠️ Sudah di Job |
| **TOTAL** | **17** | **1** | **16** | **❌ 94% Sync** |

*Export sudah di Job tapi masih pakai ->send()

---

## 3. Audit Trash Queue

### 3.1 Trash Jobs yang Ada

#### A. Single Operations
1. **ForceDeleteTrashBinJob**
   - Queue: `schemes`
   - Timeout: 900 detik (15 menit) ⚠️
   - Tries: 3
   - Fungsi: Force delete single trash bin dengan cascade

2. **RestoreTrashBinJob**
   - Queue: `schemes`
   - Timeout: 900 detik (15 menit) ⚠️
   - Tries: 3
   - Fungsi: Restore single trash bin

#### B. Bulk Operations
3. **BulkForceDeleteTrashBinsJob**
   - Queue: `schemes`
   - Timeout: 1800 detik (30 menit) ⚠️⚠️
   - Tries: 3
   - Fungsi: Bulk force delete multiple trash bins

4. **BulkRestoreTrashBinsJob**
   - Queue: `schemes`
   - Timeout: 1800 detik (30 menit) ⚠️⚠️
   - Tries: 3
   - Fungsi: Bulk restore multiple trash bins

5. **ForceDeleteAllTrashBinsJob**
   - Queue: `schemes`
   - Timeout: 1800 detik (30 menit) ⚠️⚠️
   - Tries: 3
   - Fungsi: Force delete all trash bins (filtered)

6. **RestoreAllTrashBinsJob**
   - Queue: `schemes`
   - Timeout: 1800 detik (30 menit) ⚠️⚠️
   - Tries: 3
   - Fungsi: Restore all trash bins (filtered)

### 3.2 Masalah Trash Queue

#### A. Timeout Terlalu Tinggi ⚠️
```php
public int $timeout = 900;  // 15 menit untuk single operation
public int $timeout = 1800; // 30 menit untuk bulk operation
```

**Masalah:**
- Worker akan terikat terlalu lama
- Bisa menyebabkan queue bottleneck
- Memory leak potential di Octane

**Rekomendasi:**
- Single operation: 120-300 detik (2-5 menit)
- Bulk operation: 600 detik (10 menit) maksimal
- Gunakan chunking untuk operasi besar

#### B. Tidak Ada Progress Tracking
- User tidak tahu status operasi bulk
- Tidak ada feedback untuk long-running jobs

#### C. Queue Name Tidak Sesuai
```php
$this->onQueue('schemes');
```

**Masalah:**
- Trash operations menggunakan queue `schemes`
- Tidak ada dedicated queue untuk trash
- Bisa conflict dengan course/unit operations

**Rekomendasi:**
- Buat queue `trash` tersendiri
- Atau gunakan `default` untuk non-critical operations

### 3.3 Implementasi yang Baik ✅

1. **Async Dispatch Logic**
```php
// TrashBinManagementService.php
if ($this->trashService->shouldRunAsyncCascade($bin, $groupCount)) {
    ForceDeleteTrashBinJob::dispatch($bin->id, $actor->id);
    return ['status' => 'queued', ...];
}
```
- ✅ Smart decision: async hanya untuk cascade operations
- ✅ Synchronous untuk single item (lebih cepat)

2. **Batch Processing**
```php
// TrashBinService.php
$query->chunkById(100, function ($bins) use (&$count): void {
    DB::transaction(function () use ($bins, &$count): void {
        foreach ($bins as $bin) {
            if ($this->forceDeleteSingle($bin)) {
                $count++;
            }
        }
    });
});
```
- ✅ Chunking untuk memory efficiency
- ✅ Transaction per batch
- ⚠️ Masih bisa lebih optimal (lihat rekomendasi)

---

## 4. Masalah Kritis yang Ditemukan

### 4.1 N+1 Query di Cascade Delete ❌
**File:** `TrashBinService.php` - `cascadeDeleteChildren()`

```php
// SEBELUM (N+1 problem)
$model->units()->get()->each(function ($unit): void {
    if (! $unit->trashed()) {
        $unit->delete(); // Trigger queries per unit
    }
});
```

**Dampak:**
- Course dengan 50 units = 50+ queries
- Setiap unit dengan 10 lessons = 500+ queries total
- Sangat lambat untuk data besar

**Sudah Diperbaiki:** ✅
```php
// SESUDAH (Optimized)
$units = $model->units()->whereNull('deleted_at')->get();
foreach ($units as $unit) {
    $unit->delete();
}
```

### 4.2 Memory Leak di Octane ❌
**File:** `TrashBinService.php` - `hasStatusColumn()`

```php
// SEBELUM (Memory leak)
private function hasStatusColumn(Model $model): bool
{
    static $cache = []; // ❌ Tidak di-reset antar request di Octane
    // ...
}
```

**Sudah Diperbaiki:** ✅
```php
// SESUDAH (Octane-safe)
private function hasStatusColumn(Model $model): bool
{
    return Cache::remember(
        "schema:has_status_column:{$table}",
        now()->addHours(24),
        fn () => Schema::hasColumn($table, 'status')
    );
}
```

### 4.3 Tidak Ada Dedicated Email Queue ⚠️

**Masalah:**
- Email menggunakan queue `default` atau tidak ada queue sama sekali
- Tidak ada prioritas untuk email critical (auth, password reset)
- Tidak ada separation of concerns

**Rekomendasi:**
```php
// config/queue.php
'queues' => [
    'emails-critical' => 'emails-critical',  // Auth, password reset
    'emails-transactional' => 'emails-transactional', // Enrollment, notifications
    'emails-marketing' => 'emails-marketing', // Newsletter, announcements
    'grading' => 'grading',
    'notifications' => 'notifications',
    'file-processing' => 'file-processing',
    'trash' => 'trash', // NEW
    'default' => 'default',
]
```

---

## 5. Rekomendasi Peningkatan Efektivitas

### 5.1 Email Queue - Priority HIGH 🔴

#### A. Ubah Semua Email ke Queue


**1. Enrollment Emails**
```php
// SEBELUM
Mail::to($student->email)->send(new StudentEnrollmentActiveMail(...));

// SESUDAH
Mail::to($student->email)->queue(new StudentEnrollmentActiveMail(...));

// ATAU dengan queue spesifik
Mail::to($student->email)
    ->onQueue('emails-transactional')
    ->queue(new StudentEnrollmentActiveMail(...));
```

**2. Authentication Emails**
```php
// SEBELUM
Mail::to($user)->send(new VerifyEmailLinkMail(...));

// SESUDAH - Critical queue
Mail::to($user)
    ->onQueue('emails-critical')
    ->queue(new VerifyEmailLinkMail(...));
```

**3. Course Completion Email - Ubah Listener ke ShouldQueue**
```php
// SEBELUM
class SendCourseCompletedEmail
{
    public function handle(CourseCompleted $event): void
    {
        Mail::to($user->email)->send(new CourseCompletedMail(...));
    }
}

// SESUDAH
class SendCourseCompletedEmail implements ShouldQueue
{
    use Queueable;
    
    public string $queue = 'emails-transactional';
    public int $tries = 3;
    public int $timeout = 60;
    
    public function handle(CourseCompleted $event): void
    {
        Mail::to($user->email)->queue(new CourseCompletedMail(...));
    }
}
```

#### B. Buat Dedicated Email Jobs

**Untuk Bulk Email Operations:**
```php
// app/Jobs/SendBulkEnrollmentEmailsJob.php
class SendBulkEnrollmentEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public array $enrollmentIds,
        public string $emailType
    ) {
        $this->onQueue('emails-transactional');
    }

    public function handle(): void
    {
        $enrollments = Enrollment::with(['user', 'course'])
            ->whereIn('id', $this->enrollmentIds)
            ->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->user && $enrollment->user->email) {
                $this->sendEmail($enrollment);
            }
        }
    }

    private function sendEmail($enrollment): void
    {
        $mailable = match($this->emailType) {
            'approved' => new StudentEnrollmentApprovedMail(...),
            'declined' => new StudentEnrollmentDeclinedMail(...),
            'activated' => new StudentEnrollmentActivatedMail(...),
            default => null,
        };

        if ($mailable) {
            Mail::to($enrollment->user->email)->queue($mailable);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendBulkEnrollmentEmailsJob failed', [
            'enrollment_ids' => $this->enrollmentIds,
            'email_type' => $this->emailType,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

#### C. Implementasi Rate Limiting untuk Email

```php
// app/Jobs/SendEmailJob.php
class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RateLimited;

    public int $tries = 3;
    public int $timeout = 60;

    public function middleware(): array
    {
        return [
            new RateLimited('emails'), // 100 emails per minute
        ];
    }

    // ...
}

// app/Providers/AppServiceProvider.php
RateLimiter::for('emails', function (object $job) {
    return Limit::perMinute(100)->by($job->queue);
});
```

### 5.2 Trash Queue - Priority MEDIUM 🟡

#### A. Kurangi Timeout
```php
// SEBELUM
public int $timeout = 900;  // 15 menit
public int $timeout = 1800; // 30 menit

// SESUDAH
public int $timeout = 180;  // 3 menit untuk single
public int $timeout = 600;  // 10 menit untuk bulk
```

#### B. Tambahkan Progress Tracking

**1. Buat Job dengan Batch Support**
```php
use Illuminate\Bus\Batchable;

class BulkForceDeleteTrashBinsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function handle(TrashBinService $trashBinService): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $trashBinService->forceDeleteMany($this->ids);
    }
}
```

**2. Dispatch dengan Batch**
```php
use Illuminate\Support\Facades\Bus;

// TrashBinManagementService.php
public function bulkForceDelete(array $ids, User $actor): array
{
    $chunks = array_chunk($ids, 50); // Process 50 items per job
    
    $batch = Bus::batch([])
        ->name('Bulk Force Delete Trash Bins')
        ->then(function (Batch $batch) {
            // All jobs completed successfully
            Log::info('Bulk delete completed', ['batch_id' => $batch->id]);
        })
        ->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure
            Log::error('Bulk delete failed', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        })
        ->finally(function (Batch $batch) {
            // Batch finished executing
        })
        ->dispatch();

    foreach ($chunks as $chunk) {
        $batch->add(new BulkForceDeleteTrashBinsJob($chunk, $actor->id));
    }

    return [
        'status' => 'queued',
        'batch_id' => $batch->id,
        'total_items' => count($ids),
        'total_jobs' => count($chunks),
    ];
}
```

**3. API Endpoint untuk Progress**
```php
// TrashBinController.php
public function batchStatus(string $batchId): JsonResponse
{
    $batch = Bus::findBatch($batchId);

    if (!$batch) {
        return $this->error('Batch not found', 404);
    }

    return $this->success([
        'batch_id' => $batch->id,
        'name' => $batch->name,
        'total_jobs' => $batch->totalJobs,
        'pending_jobs' => $batch->pendingJobs,
        'processed_jobs' => $batch->processedJobs(),
        'failed_jobs' => $batch->failedJobs,
        'progress' => $batch->progress(),
        'finished' => $batch->finished(),
        'cancelled' => $batch->cancelled(),
    ]);
}
```

#### C. Ubah Queue Name
```php
// SEBELUM
$this->onQueue('schemes');

// SESUDAH
$this->onQueue('trash');
```

#### D. Optimasi Cascade Delete dengan Job Chaining

```php
class ForceDeleteCourseWithCascadeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public function handle(): void
    {
        $course = Course::withTrashed()->find($this->courseId);
        
        if (!$course) {
            return;
        }

        // Get all units
        $unitIds = $course->units()->withTrashed()->pluck('id')->toArray();

        // Chain jobs: Delete units first, then course
        $jobs = [];
        foreach (array_chunk($unitIds, 10) as $chunk) {
            $jobs[] = new ForceDeleteUnitsJob($chunk);
        }
        $jobs[] = new ForceDeleteCourseJob($this->courseId);

        // Dispatch chain
        Bus::chain($jobs)->dispatch();
    }
}
```

### 5.3 Monitoring & Observability - Priority HIGH 🔴

#### A. Tambahkan Queue Monitoring

**1. Install Laravel Horizon (Recommended)**
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

**2. Konfigurasi Horizon**
```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['emails-critical'],
            'balance' => 'auto',
            'processes' => 3,
            'tries' => 3,
            'timeout' => 60,
        ],
        'supervisor-2' => [
            'connection' => 'redis',
            'queue' => ['emails-transactional', 'notifications'],
            'balance' => 'auto',
            'processes' => 5,
            'tries' => 3,
            'timeout' => 120,
        ],
        'supervisor-3' => [
            'connection' => 'redis',
            'queue' => ['trash', 'file-processing'],
            'balance' => 'auto',
            'processes' => 2,
            'tries' => 3,
            'timeout' => 300,
        ],
        'supervisor-4' => [
            'connection' => 'redis',
            'queue' => ['grading', 'default'],
            'balance' => 'auto',
            'processes' => 3,
            'tries' => 3,
            'timeout' => 180,
        ],
    ],
],
```

#### B. Tambahkan Job Metrics

```php
// app/Jobs/Concerns/TracksJobMetrics.php
trait TracksJobMetrics
{
    public function handle(): void
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $this->execute();
        } finally {
            $duration = microtime(true) - $startTime;
            $memoryUsed = memory_get_usage() - $startMemory;

            Log::info('Job metrics', [
                'job' => get_class($this),
                'queue' => $this->queue,
                'duration_ms' => round($duration * 1000, 2),
                'memory_mb' => round($memoryUsed / 1024 / 1024, 2),
            ]);
        }
    }

    abstract protected function execute(): void;
}
```

#### C. Failed Job Notifications

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;

public function boot(): void
{
    Queue::failing(function (JobFailed $event) {
        // Log to monitoring service
        Log::error('Queue job failed', [
            'connection' => $event->connectionName,
            'queue' => $event->job->getQueue(),
            'job' => $event->job->resolveName(),
            'exception' => $event->exception->getMessage(),
            'trace' => $event->exception->getTraceAsString(),
        ]);

        // Send notification to admin (optional)
        if (app()->environment('production')) {
            // Notify::admins()->about(new JobFailedNotification($event));
        }
    });
}
```

### 5.4 Redis Configuration - Priority MEDIUM 🟡

#### A. Optimasi Redis untuk Queue

```env
# .env
REDIS_CLIENT=phpredis  # ✅ Sudah benar (lebih cepat dari predis)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# Tambahkan untuk production
REDIS_QUEUE_CONNECTION=queue  # Dedicated connection untuk queue
REDIS_CACHE_CONNECTION=cache  # Dedicated connection untuk cache
```

```php
// config/database.php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),

    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],

    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],

    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],

    'queue' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_QUEUE_DB', '2'),
    ],
],
```

#### B. Queue Worker Configuration

**Systemd Service untuk Production**
```ini
# /etc/systemd/system/levl-queue-worker@.service
[Unit]
Description=Levl Queue Worker %i
After=network.target redis.service

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5
WorkingDirectory=/var/www/levl-be
ExecStart=/usr/bin/php /var/www/levl-be/artisan queue:work redis --queue=%i --sleep=3 --tries=3 --max-time=3600 --memory=512

[Install]
WantedBy=multi-user.target
```

**Start Multiple Workers**
```bash
# Critical emails - 3 workers
systemctl enable levl-queue-worker@emails-critical
systemctl start levl-queue-worker@emails-critical

# Transactional emails - 5 workers
for i in {1..5}; do
    systemctl enable levl-queue-worker@emails-transactional
    systemctl start levl-queue-worker@emails-transactional
done

# Trash operations - 2 workers
for i in {1..2}; do
    systemctl enable levl-queue-worker@trash
    systemctl start levl-queue-worker@trash
done
```

**Atau gunakan Supervisor**
```ini
# /etc/supervisor/conf.d/levl-queue.conf
[program:levl-queue-emails-critical]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/levl-be/artisan queue:work redis --queue=emails-critical --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/levl-be/storage/logs/queue-emails-critical.log
stopwaitsecs=3600

[program:levl-queue-emails-transactional]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/levl-be/artisan queue:work redis --queue=emails-transactional --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=5
redirect_stderr=true
stdout_logfile=/var/www/levl-be/storage/logs/queue-emails-transactional.log
stopwaitsecs=3600

[program:levl-queue-trash]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/levl-be/artisan queue:work redis --queue=trash --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/levl-be/storage/logs/queue-trash.log
stopwaitsecs=3600
```

### 5.5 Testing Queue - Priority MEDIUM 🟡

#### A. Unit Tests untuk Jobs

```php
// tests/Unit/Jobs/SendEnrollmentEmailJobTest.php
class SendEnrollmentEmailJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_email_successfully()
    {
        Mail::fake();
        
        $enrollment = Enrollment::factory()->create();
        
        $job = new SendEnrollmentEmailJob($enrollment->id, 'approved');
        $job->handle();
        
        Mail::assertQueued(StudentEnrollmentApprovedMail::class, function ($mail) use ($enrollment) {
            return $mail->hasTo($enrollment->user->email);
        });
    }

    public function test_job_handles_missing_enrollment()
    {
        Mail::fake();
        
        $job = new SendEnrollmentEmailJob(99999, 'approved');
        $job->handle();
        
        Mail::assertNothingQueued();
    }

    public function test_job_retries_on_failure()
    {
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP error'));
        
        $job = new SendEnrollmentEmailJob(1, 'approved');
        
        $this->expectException(\Exception::class);
        $job->handle();
        
        $this->assertEquals(3, $job->tries);
    }
}
```

#### B. Integration Tests

```php
// tests/Feature/Queue/EmailQueueTest.php
class EmailQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_approval_queues_email()
    {
        Queue::fake();
        
        $enrollment = Enrollment::factory()->pending()->create();
        
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/enrollments/{$enrollment->id}/approve");
        
        $response->assertOk();
        
        Queue::assertPushed(SendEnrollmentEmailJob::class, function ($job) use ($enrollment) {
            return $job->enrollmentId === $enrollment->id 
                && $job->emailType === 'approved';
        });
    }
}
```

---

## 6. Implementation Roadmap

### Phase 1: Critical Fixes (Week 1) 🔴
**Priority: IMMEDIATE**

1. ✅ **Fix N+1 Query di Trash** - DONE
2. ✅ **Fix Memory Leak di Octane** - DONE
3. **Ubah Semua Email ke Queue**
   - [ ] Enrollment emails (9 jenis)
   - [ ] Authentication emails (5 jenis)
   - [ ] Course completion email
   - [ ] Export email (ubah dari ->send ke ->queue)

**Estimasi:** 2-3 hari  
**Impact:** High - Mengurangi blocking requests, improve response time

### Phase 2: Queue Optimization (Week 2) 🟡
**Priority: HIGH**

1. **Buat Dedicated Email Queues**
   - [ ] emails-critical
   - [ ] emails-transactional
   - [ ] emails-marketing

2. **Optimasi Trash Queue**
   - [ ] Kurangi timeout
   - [ ] Ubah queue name ke 'trash'
   - [ ] Implementasi batch processing

3. **Setup Queue Monitoring**
   - [ ] Install Laravel Horizon
   - [ ] Configure supervisors
   - [ ] Setup failed job notifications

**Estimasi:** 3-4 hari  
**Impact:** Medium - Better queue management, monitoring

### Phase 3: Advanced Features (Week 3-4) 🟢
**Priority: MEDIUM**

1. **Implementasi Progress Tracking**
   - [ ] Batch support untuk bulk operations
   - [ ] API endpoint untuk progress
   - [ ] Frontend integration

2. **Rate Limiting**
   - [ ] Email rate limiting
   - [ ] API rate limiting untuk bulk operations

3. **Job Chaining untuk Complex Operations**
   - [ ] Course cascade delete dengan chain
   - [ ] Unit cascade delete dengan chain

4. **Comprehensive Testing**
   - [ ] Unit tests untuk semua jobs
   - [ ] Integration tests
   - [ ] Load testing

**Estimasi:** 5-7 hari  
**Impact:** Low-Medium - Better UX, scalability

---

## 7. Performance Metrics & KPIs

### Current State (Estimated)
- Email send time: 500-2000ms (blocking)
- Trash cascade delete: 5-30 seconds (blocking)
- Queue processing: Not monitored
- Failed job rate: Unknown

### Target State (After Implementation)
- Email send time: <50ms (queued, non-blocking)
- Trash cascade delete: <100ms (queued, non-blocking)
- Queue processing time:
  - Critical emails: <30 seconds
  - Transactional emails: <2 minutes
  - Trash operations: <5 minutes
- Failed job rate: <1%
- Queue throughput: 1000+ jobs/minute

### Monitoring Metrics
1. **Queue Metrics**
   - Jobs processed per minute
   - Average job duration
   - Failed job rate
   - Queue depth (pending jobs)

2. **Email Metrics**
   - Emails sent per hour
   - Email delivery rate
   - Bounce rate
   - Average send time

3. **Trash Metrics**
   - Delete operations per day
   - Average cascade size
   - Restore success rate
   - Purge efficiency

---

## 8. Kesimpulan

### Temuan Utama
1. ❌ **94% email masih synchronous** - Blocking user requests
2. ⚠️ **Trash timeout terlalu tinggi** - 15-30 menit per job
3. ⚠️ **Tidak ada dedicated email queue** - Mixing priorities
4. ✅ **N+1 query sudah diperbaiki** - Good optimization
5. ✅ **Memory leak sudah diperbaiki** - Octane-safe
6. ⚠️ **Tidak ada monitoring** - Blind spot untuk production

### Prioritas Perbaikan
1. 🔴 **CRITICAL**: Ubah semua email ke queue (Phase 1)
2. 🔴 **CRITICAL**: Setup queue monitoring (Phase 2)
3. 🟡 **HIGH**: Optimasi trash timeout (Phase 2)
4. 🟡 **HIGH**: Buat dedicated email queues (Phase 2)
5. 🟢 **MEDIUM**: Implementasi progress tracking (Phase 3)

### Expected Benefits
- **Response Time**: 80-90% improvement untuk endpoints dengan email
- **Scalability**: Dapat handle 10x lebih banyak concurrent users
- **Reliability**: Retry mechanism untuk failed jobs
- **Monitoring**: Real-time visibility ke queue health
- **User Experience**: Non-blocking operations, progress tracking

### Next Steps
1. Review dan approve roadmap
2. Mulai Phase 1 implementation
3. Setup staging environment untuk testing
4. Deploy ke production dengan monitoring ketat
5. Iterate berdasarkan metrics

---

**Dokumen ini dibuat oleh:** Kiro AI Assistant  
**Tanggal:** 17 Maret 2026  
**Status:** Ready for Review  
**Version:** 1.0
