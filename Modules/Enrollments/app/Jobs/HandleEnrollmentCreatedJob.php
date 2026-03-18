<?php

declare(strict_types=1);

namespace Modules\Enrollments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Enrollments\Models\Enrollment;
use Throwable;

class HandleEnrollmentCreatedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public array $backoff = [5, 30, 120];

    public function __construct(
        private readonly int $enrollmentId,
        private readonly string $status,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $enrollment = Enrollment::find($this->enrollmentId);

        if (! $enrollment || ! $enrollment->course || ! $enrollment->user) {
            return;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('HandleEnrollmentCreatedJob failed', [
            'enrollment_id' => $this->enrollmentId,
            'error' => $exception->getMessage(),
        ]);
    }
}
