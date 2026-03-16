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

