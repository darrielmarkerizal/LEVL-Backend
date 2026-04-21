<?php

declare(strict_types=1);

namespace Modules\Grading\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkReleaseGradesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public array $targets,
        public ?int $instructorId = null
    ) {
        $this->onQueue('grading');
    }

    public function handle(\Modules\Grading\Services\GradingBulkService $gradingService): void
    {
        if (empty($this->targets)) {
            Log::info('BulkReleaseGradesJob: No targets provided, skipping');

            return;
        }

        Log::info('BulkReleaseGradesJob: Starting bulk grade release', [
            'target_count' => count($this->targets),
            'instructor_id' => $this->instructorId,
        ]);

        try {
            $result = $gradingService->bulkReleaseGrades($this->targets, $this->instructorId);

            Log::info('BulkReleaseGradesJob: Completed bulk grade release', [
                'count' => $result,
                'instructor_id' => $this->instructorId,
            ]);
        } catch (\Throwable $e) {
            Log::error('BulkReleaseGradesJob: Failed to release grades', [
                'targets' => $this->targets,
                'instructor_id' => $this->instructorId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('BulkReleaseGradesJob: Job failed after all retries', [
            'targets' => $this->targets,
            'instructor_id' => $this->instructorId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    public function tags(): array
    {
        return [
            'bulk-release-grades',
            'instructor:'.($this->instructorId ?? 'unknown'),
            'targets:'.count($this->targets),
        ];
    }
}
