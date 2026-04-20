<?php

declare(strict_types=1);

namespace Modules\Enrollments\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Events\EnrollmentActivated;
use Modules\Enrollments\Models\Enrollment;
use Modules\Mail\Mail\Enrollments\StudentEnrollmentActivatedMail;
use Throwable;

class ActivateScheduledEnrollmentsCommand extends Command
{
    protected $signature = 'enrollments:activate-scheduled';

    protected $description = 'Activate enrollments that are scheduled for today or past dates';

    public function handle(): int
    {
        $this->info('Checking for scheduled enrollments to activate...');

        $now = Carbon::now();

        
        $enrollments = Enrollment::query()
            ->useWritePdo()
            ->where('status', EnrollmentStatus::Pending)
            ->where('auto_activate_on_enrolled_at', true)
            ->whereDate('enrolled_at', '<=', $now->toDateString())
            ->with(['user:id,name,email', 'course:id,title,slug,code'])
            ->get();

        if ($enrollments->isEmpty()) {
            $this->info('No scheduled enrollments to activate.');

            return self::SUCCESS;
        }

        $activated = 0;
        $failed = 0;

        foreach ($enrollments as $enrollment) {
            try {
                $this->resetFailedTransactionState();

                $updatedRows = DB::transaction(function () use ($enrollment) {
                    return Enrollment::query()
                        ->whereKey($enrollment->id)
                        ->where('status', EnrollmentStatus::Pending->value)
                        ->update([
                            'status' => EnrollmentStatus::Active->value,
                            'auto_activate_on_enrolled_at' => false,
                            'updated_at' => now(),
                        ]);
                });

                if ($updatedRows === 0) {
                    continue;
                }

                
                EnrollmentActivated::dispatch($enrollment);

                if ($enrollment->user?->email && $enrollment->course?->slug) {
                    $courseUrl = config('app.frontend_url').'/courses/'.$enrollment->course->slug;
                    Mail::to($enrollment->user->email)
                        ->queue((new StudentEnrollmentActivatedMail(
                            $enrollment->user,
                            $enrollment->course,
                            $courseUrl
                        ))->onQueue('emails-transactional'));
                }

                $activated++;
                $studentName = $enrollment->user?->name ?? 'unknown user';
                $this->info("✓ Activated enrollment #{$enrollment->id} for {$studentName}");
            } catch (Throwable $e) {
                $failed++;
                $this->error("✗ Failed to activate enrollment #{$enrollment->id}: {$e->getMessage()}");
                Log::error('ActivateScheduledEnrollmentsCommand failed for enrollment', [
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ]);

                $this->resetFailedTransactionState();
            }
        }

        $this->info("\nSummary:");
        $this->info("Activated: {$activated}");
        if ($failed > 0) {
            $this->warn("Failed: {$failed}");
        }

        return self::SUCCESS;
    }

    private function resetFailedTransactionState(): void
    {
        try {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        } catch (Throwable) {
            
        }

        try {
            DB::select('select 1');
        } catch (QueryException $e) {
            if (! $this->isFailedTransactionError($e)) {
                throw $e;
            }

            DB::disconnect();
            DB::purge();
            DB::reconnect();
        }
    }

    private function isFailedTransactionError(Throwable $e): bool
    {
        return str_contains($e->getMessage(), '25P02')
            || str_contains(strtolower($e->getMessage()), 'failed sql transaction')
            || str_contains(strtolower($e->getMessage()), 'current transaction is aborted');
    }
}
