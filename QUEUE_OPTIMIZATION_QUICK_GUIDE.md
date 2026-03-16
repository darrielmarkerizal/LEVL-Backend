# Queue Optimization - Quick Implementation Guide

**Target:** Mengubah email synchronous menjadi queue-based  
**Estimasi:** 2-3 hari untuk Phase 1

---

## 1. Quick Wins - Ubah Email ke Queue (30 menit)

### A. Enrollment Emails
**File:** `Modules/Enrollments/app/Services/Support/EnrollmentLifecycleProcessor.php`

```php
// SEBELUM (line 185, 187, 189, 257, 281, 327, 329, 342)
Mail::to($student->email)->send(new StudentEnrollmentActiveMail(...));

// SESUDAH
Mail::to($student->email)
    ->onQueue('emails-transactional')
    ->queue(new StudentEnrollmentActiveMail(...));
```

**Total Changes:** 9 lokasi di file yang sama

### B. Authentication Emails
**File:** `Modules/Auth/app/Services/Support/VerificationTokenManager.php`

```php
// SEBELUM (line 54, 94)
Mail::to($user)->send(new VerifyEmailLinkMail(...));

// SESUDAH
Mail::to($user)
    ->onQueue('emails-critical')
    ->queue(new VerifyEmailLinkMail(...));
```

**File:** `Modules/Auth/app/Services/Support/UserLifecycleProcessor.php`
```php
// Line 292
Mail::to($user->email)
    ->onQueue('emails-critical')
    ->queue(new UserCredentialsMail(...));
```

**File:** `Modules/Auth/app/Services/AccountDeletionService.php`
```php
// Line 59
Mail::to($user->email)
    ->onQueue('emails-critical')
    ->queue(new AccountDeletionVerificationMail(...));
```

**File:** `Modules/Auth/app/Http/Controllers/PasswordResetController.php`
```php
// Line 55
Mail::to($user)
    ->onQueue('emails-critical')
    ->queue(new ResetPasswordMail(...));
```

### C. Course Completion Email
**File:** `Modules/Schemes/app/Listeners/SendCourseCompletedEmail.php`


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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

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

---

## 2. Update Queue Configuration (10 menit)

**File:** `config/queue.php`

```php
'queues' => [
    'emails-critical' => env('QUEUE_EMAILS_CRITICAL', 'emails-critical'),
    'emails-transactional' => env('QUEUE_EMAILS_TRANSACTIONAL', 'emails-transactional'),
    'grading' => env('QUEUE_GRADING', 'grading'),
    'notifications' => env('QUEUE_NOTIFICATIONS', 'notifications'),
    'file-processing' => env('QUEUE_FILE_PROCESSING', 'file-processing'),
    'trash' => env('QUEUE_TRASH', 'trash'),
    'default' => env('QUEUE_DEFAULT', 'default'),
],
```

**File:** `.env`

```env
QUEUE_EMAILS_CRITICAL=emails-critical
QUEUE_EMAILS_TRANSACTIONAL=emails-transactional
QUEUE_TRASH=trash
```

---

## 3. Update Trash Queue Names (5 menit)

**Files:** Semua file di `Modules/Trash/app/Jobs/`

```php
// SEBELUM
$this->onQueue('schemes');

// SESUDAH
$this->onQueue('trash');
```

**Affected Files:**
- ForceDeleteTrashBinJob.php
- RestoreTrashBinJob.php
- BulkForceDeleteTrashBinsJob.php
- BulkRestoreTrashBinsJob.php
- ForceDeleteAllTrashBinsJob.php
- RestoreAllTrashBinsJob.php

---

## 4. Reduce Trash Timeouts (5 menit)

```php
// SEBELUM
public int $timeout = 900;  // Single operations
public int $timeout = 1800; // Bulk operations

// SESUDAH
public int $timeout = 180;  // Single operations (3 menit)
public int $timeout = 600;  // Bulk operations (10 menit)
```

---

## 5. Start Queue Workers

### Development (Local)
```bash
# Terminal 1 - Critical emails
php artisan queue:work redis --queue=emails-critical --tries=3

# Terminal 2 - Transactional emails
php artisan queue:work redis --queue=emails-transactional --tries=3

# Terminal 3 - Trash operations
php artisan queue:work redis --queue=trash --tries=3

# Terminal 4 - Default
php artisan queue:work redis --queue=default --tries=3
```

### Production (Supervisor)
**File:** `/etc/supervisor/conf.d/levl-queue.conf`

```ini
[program:levl-queue-emails-critical]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/levl-be/artisan queue:work redis --queue=emails-critical --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/levl-be/storage/logs/queue-emails-critical.log

[program:levl-queue-emails-transactional]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/levl-be/artisan queue:work redis --queue=emails-transactional --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=5
redirect_stderr=true
stdout_logfile=/var/www/levl-be/storage/logs/queue-emails-transactional.log

[program:levl-queue-trash]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/levl-be/artisan queue:work redis --queue=trash --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/levl-be/storage/logs/queue-trash.log
```

**Commands:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start levl-queue-emails-critical:*
sudo supervisorctl start levl-queue-emails-transactional:*
sudo supervisorctl start levl-queue-trash:*
```

---

## 6. Testing Checklist

### Manual Testing
- [ ] Test enrollment approval email (queued)
- [ ] Test password reset email (queued)
- [ ] Test email verification (queued)
- [ ] Test course completion email (queued)
- [ ] Test trash delete operation (queued)
- [ ] Test trash restore operation (queued)
- [ ] Check failed_jobs table (should be empty)
- [ ] Monitor queue depth in Redis

### Commands untuk Testing
```bash
# Check queue status
php artisan queue:monitor redis:emails-critical,redis:emails-transactional,redis:trash

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

---

## 7. Monitoring Commands

```bash
# Real-time queue monitoring
php artisan queue:monitor redis:emails-critical,redis:emails-transactional,redis:trash --max=100

# Check Redis queue size
redis-cli LLEN queues:emails-critical
redis-cli LLEN queues:emails-transactional
redis-cli LLEN queues:trash

# Watch queue processing
watch -n 1 'redis-cli LLEN queues:emails-critical && redis-cli LLEN queues:emails-transactional'
```

---

## 8. Rollback Plan

Jika ada masalah, rollback dengan:

```php
// Ubah kembali ke synchronous
Mail::to($user->email)->send(new SomeMail(...));

// Stop queue workers
sudo supervisorctl stop levl-queue-emails-critical:*
sudo supervisorctl stop levl-queue-emails-transactional:*
```

---

## 9. Performance Comparison

### Before (Synchronous)
```
POST /api/v1/enrollments/{id}/approve
Response Time: 1500-3000ms (with email send)
```

### After (Queue)
```
POST /api/v1/enrollments/{id}/approve
Response Time: 50-200ms (email queued)
Email Sent: 1-5 seconds later (background)
```

**Improvement:** 90-95% faster response time

---

## 10. Common Issues & Solutions

### Issue: Jobs not processing
```bash
# Check if workers are running
ps aux | grep "queue:work"

# Check Redis connection
redis-cli ping

# Restart workers
sudo supervisorctl restart levl-queue-emails-critical:*
```

### Issue: Failed jobs accumulating
```bash
# Check failed jobs
php artisan queue:failed

# Retry specific job
php artisan queue:retry {job-id}

# Retry all failed jobs
php artisan queue:retry all
```

### Issue: Queue too deep
```bash
# Check queue depth
redis-cli LLEN queues:emails-transactional

# Add more workers temporarily
php artisan queue:work redis --queue=emails-transactional --tries=3
```

---

## Summary

**Total Changes Required:**
- 17 email send locations → queue
- 6 trash job queue names
- 6 trash job timeouts
- 1 listener → ShouldQueue
- 1 config file update

**Estimated Time:** 2-3 hours untuk implementation  
**Testing Time:** 1-2 hours  
**Total:** Half day untuk Phase 1

**Expected Impact:**
- 90% faster API response times
- Better scalability
- Automatic retry for failed emails
- Non-blocking user experience
