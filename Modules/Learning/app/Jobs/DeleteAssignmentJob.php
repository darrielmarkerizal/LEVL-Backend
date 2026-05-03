<?php

declare(strict_types=1);

namespace Modules\Learning\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Learning\Contracts\Services\AssignmentServiceInterface;
use Throwable;

class DeleteAssignmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public int $timeout = 900;

    public array $backoff = [5, 30, 120];

    public function __construct(
        public int $assignmentId,
        public ?int $actorId = null,
    ) {
        $this->onQueue('trash');
    }

    public function handle(AssignmentServiceInterface $assignmentService): void
    {
        if ($this->actorId) {
            $user = \Modules\Auth\Models\User::find($this->actorId);

            if ($user) {
                auth()->setUser($user);
                auth('api')->setUser($user);
            }
        }

        try {
            $assignment = \Modules\Learning\Models\Assignment::query()->findOrFail($this->assignmentId);
            $assignmentService->delete($assignment);
        } catch (ModelNotFoundException) {
            Log::info('DeleteAssignmentJob: assignment already deleted, skipping', [
                'assignment_id' => $this->assignmentId,
                'actor_id' => $this->actorId,
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('DeleteAssignmentJob failed', [
            'assignment_id' => $this->assignmentId,
            'actor_id' => $this->actorId,
            'error' => $exception->getMessage(),
        ]);
    }
}