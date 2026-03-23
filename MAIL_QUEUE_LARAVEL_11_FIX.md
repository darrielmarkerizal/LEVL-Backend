# Mail Queue Laravel 11 Compatibility Fix

## Masalah

Laravel 11 menghapus method `onQueue()` dari `PendingMail` class. Pattern lama yang menggunakan:

```php
Mail::to($user)
    ->onQueue('queue-name')
    ->queue(new SomeMailable());
```

Akan menghasilkan error:
```
Call to undefined method Illuminate\Mail\PendingMail::onQueue()
```

## Solusi

Method `onQueue()` harus dipanggil pada mailable instance, bukan pada PendingMail:

```php
Mail::to($user)
    ->queue((new SomeMailable())->onQueue('queue-name'));
```

## File yang Diperbaiki

### 1. Auth Module
- ✅ `Modules/Auth/app/Services/Support/VerificationTokenManager.php`
  - `sendVerificationLink()` - emails-critical queue
  - `sendChangeEmailLink()` - emails-critical queue

- ✅ `Modules/Auth/app/Services/Support/UserLifecycleProcessor.php`
  - `sendCredentials()` - emails-critical queue

- ✅ `Modules/Auth/app/Services/AccountDeletionService.php`
  - `sendVerificationLink()` - emails-critical queue

- ✅ `Modules/Auth/app/Jobs/ExportUsersToEmailJob.php`
  - `handle()` - emails-transactional queue

- ✅ `Modules/Auth/app/Http/Controllers/PasswordResetController.php`
  - `sendResetLink()` - emails-critical queue

### 2. Enrollments Module
- ✅ `Modules/Enrollments/app/Console/ActivateScheduledEnrollmentsCommand.php`
  - Activation notification - emails-transactional queue

- ✅ `Modules/Enrollments/app/Services/Support/EnrollmentLifecycleProcessor.php`
  - `create()` - Scheduled/Active/Pending notifications - emails-transactional queue
  - `approve()` - Approval notification - emails-transactional queue
  - `decline()` - Decline notification - emails-transactional queue
  - `notifyStudent()` - Active/Pending notifications - emails-transactional queue
  - `notifyCourseManagers()` - Admin notifications - emails-transactional queue

### 3. Schemes Module
- ✅ `Modules/Schemes/app/Listeners/SendCourseCompletedEmail.php`
  - Course completion notification - emails-transactional queue

## Queue Configuration

Aplikasi menggunakan **Redis** sebagai queue driver dengan multiple named queues:

```env
QUEUE_CONNECTION=redis
```

### Queue Priority (high to low):
1. `emails-critical` - Auth emails (password reset, verification)
2. `emails-transactional` - Transactional emails (enrollment, notifications)
3. `grading` - Grade recalculation
4. `notifications` - In-app notifications, gamification XP
5. `file-processing` - File validation and storage
6. `trash` - Trash bin operations
7. `logging` - Activity logging
8. `audit` - Audit logs
9. `default` - General background tasks

### Worker Command:
```bash
php artisan queue:work --queue=emails-critical,emails-transactional,grading,notifications,file-processing,trash,logging,audit,default
```

## Testing

Setelah fix ini, test dengan:

1. **User Registration** - Verifikasi email terkirim
2. **Password Reset** - Reset password email terkirim
3. **Enrollment** - Notifikasi enrollment terkirim
4. **Course Completion** - Email completion terkirim

## Catatan Penting

- ✅ Semua file sudah diperbaiki
- ✅ Tidak ada lagi pattern `->onQueue()->queue()` yang salah
- ✅ Queue names tetap sama (emails-critical, emails-transactional)
- ✅ Kompatibel dengan Laravel 11.47.0
- ✅ Redis queue tetap berfungsi normal

## Referensi

- Laravel 11 Upgrade Guide: https://laravel.com/docs/11.x/upgrade
- Mail Queue Documentation: https://laravel.com/docs/11.x/mail#queueing-mail
