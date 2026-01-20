<?php

declare(strict_types=1);

namespace Modules\Enrollments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Enrollments\Models\Enrollment;

class HandleEnrollmentCreatedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $enrollmentId,
        private readonly string $status,
    ) {}

    public function handle(): void
    {
        $enrollment = Enrollment::find($this->enrollmentId);

        if (! $enrollment || ! $enrollment->course || ! $enrollment->user) {
            return;
        }
        // Email sending and heavy relation loading are disabled.
        // $this->notifyCourseManagers($enrollment);
    }
}
