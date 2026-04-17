<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Activitylog\Models\Activity;

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
        $payload = $this->normalizeAuditData($this->auditData);

        $activity = activity((string) $payload['log_name'])
            ->withProperties($payload['properties'])
            ->event((string) $payload['description'])
            ->log((string) $payload['description']);

        if (! empty($payload['causer_type']) && ! empty($payload['causer_id'])) {
            $causerClass = $payload['causer_type'];
            if (class_exists($causerClass)) {
                $causer = $causerClass::query()->find($payload['causer_id']);
                if ($causer) {
                    $activity->causer()->associate($causer);
                }
            }
        }

        if (! empty($payload['subject_type']) && ! empty($payload['subject_id'])) {
            $subjectClass = $payload['subject_type'];
            if (class_exists($subjectClass)) {
                $subject = $subjectClass::query()->find($payload['subject_id']);
                if ($subject) {
                    $activity->subject()->associate($subject);
                }
            }
        }

        $activity->save();
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

    /**
     * @return array{
     *     log_name: string,
     *     description: string,
     *     subject_type: class-string<\Illuminate\Database\Eloquent\Model>|null,
     *     subject_id: int|string|null,
     *     causer_type: class-string<\Illuminate\Database\Eloquent\Model>|null,
     *     causer_id: int|string|null,
     *     properties: array<string, mixed>
     * }
     */
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
