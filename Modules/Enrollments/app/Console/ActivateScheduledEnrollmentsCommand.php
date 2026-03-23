<?php

declare(strict_types=1);

namespace Modules\Enrollments\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;

class ActivateScheduledEnrollmentsCommand extends Command
{
    protected $signature = 'enrollments:activate-scheduled';

    protected $description = 'Activate enrollments that are scheduled for today or past dates';

    public function handle(): int
    {
        $this->info('Checking for scheduled enrollments to activate...');

        $now = Carbon::now();

        // Find all pending enrollments where enrolled_at is today or in the past
        $enrollments = Enrollment::where('status', EnrollmentStatus::Pending)
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
                DB::transaction(function () use ($enrollment) {
                    $enrollment->status = EnrollmentStatus::Active;
                    $enrollment->save();

                    // Dispatch event for activation
                    \Modules\Enrollments\Events\EnrollmentActivated::dispatch($enrollment);

                    // Send notification email
                    $courseUrl = config('app.frontend_url').'/courses/'.$enrollment->course->slug;
                    \Illuminate\Support\Facades\Mail::to($enrollment->user->email)
                        ->queue((new \Modules\Mail\Mail\Enrollments\StudentEnrollmentActivatedMail(
                            $enrollment->user,
                            $enrollment->course,
                            $courseUrl
                        ))->onQueue('emails-transactional'));
                });

                $activated++;
                $this->info("✓ Activated enrollment #{$enrollment->id} for {$enrollment->user->name}");
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed to activate enrollment #{$enrollment->id}: {$e->getMessage()}");
            }
        }

        $this->info("\nSummary:");
        $this->info("Activated: {$activated}");
        if ($failed > 0) {
            $this->warn("Failed: {$failed}");
        }

        return self::SUCCESS;
    }
}
