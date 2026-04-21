<?php

declare(strict_types=1);

namespace Modules\Grading\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkApplyFeedbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public array $targets,
        public string $feedback,
        public ?int $instructorId = null
    ) {
        $this->onQueue('grading');
    }

    public function handle(\Modules\Grading\Services\GradingBulkService $gradingService): void
    {
        if (empty($this->targets)) {
            Log::info('BulkApplyFeedbackJob: No targets provided, skipping');

            return;
        }

        if (empty(trim($this->feedback))) {
            Log::warning('BulkApplyFeedbackJob: Empty feedback provided, skipping');

            return;
        }

        Log::info('BulkApplyFeedbackJob: Starting bulk feedback application', [
            'target_count' => count($this->targets),
            'feedback_length' => strlen($this->feedback),
            'instructor_id' => $this->instructorId,
        ]);

        try {
            $result = $gradingService->bulkApplyFeedback($this->targets, $this->feedback, $this->instructorId);

            Log::info('BulkApplyFeedbackJob: Completed bulk feedback application', [
                
                'count' => $result,
                'instructor_id' => $this->instructorId,
            ]);
        } catch (\Throwable $e) {
            Log::error('BulkApplyFeedbackJob: Failed to apply feedback', [
                'targets' => $this->targets,
                'instructor_id' => $this->instructorId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('BulkApplyFeedbackJob: Job failed after all retries', [
            'targets' => $this->targets,
            'instructor_id' => $this->instructorId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    public function tags(): array
    {
        return [
            'bulk-apply-feedback',
            'instructor:'.($this->instructorId ?? 'unknown'),
            'targets:'.count($this->targets),
        ];
    }
}
