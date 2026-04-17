<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Common\Models\AuditLog;

class CreateAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $auditData
    ) {
        $this->onQueue('logging');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        AuditLog::create($this->normalizeAuditData($this->auditData));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to create audit log', [
            'data' => $this->auditData,
            'error' => $exception->getMessage(),
        ]);
    }

    private function normalizeAuditData(array $data): array
    {
        $context = $data['context'] ?? [];
        if (is_string($context)) {
            $decoded = json_decode($context, true);
            $context = is_array($decoded) ? $decoded : ['raw_context' => $context];
        } elseif (! is_array($context)) {
            $context = ['context' => $context];
        }

        return [
            'log_name' => (string) ($data['log_name'] ?? 'api_audit'),
            'description' => (string) ($data['description'] ?? $data['action'] ?? 'activity'),
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id' => $data['subject_id'] ?? $data['target_id'] ?? null,
            'causer_type' => $data['causer_type'] ?? $data['actor_type'] ?? null,
            'causer_id' => $data['causer_id'] ?? $data['actor_id'] ?? $data['user_id'] ?? null,
            'properties' => $context,
        ];
    }
}
