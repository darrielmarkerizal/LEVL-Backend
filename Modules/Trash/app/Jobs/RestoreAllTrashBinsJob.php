<?php

declare(strict_types=1);

namespace Modules\Trash\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Trash\Services\TrashBinService;
use Throwable;

class RestoreAllTrashBinsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public int $timeout = 900;

    public array $backoff = [10, 60, 300];

    public function __construct(
        public ?string $resourceType = null,
        public ?int $actorId = null,
        public ?int $scopedActorId = null,
        public array $accessibleCourseIds = [],
    ) {
        $this->onQueue('trash');
    }

    public function handle(TrashBinService $trashBinService): void
    {
        $trashBinService->restoreAll($this->resourceType, $this->scopedActorId, $this->accessibleCourseIds);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('RestoreAllTrashBinsJob failed', [
            'resource_type' => $this->resourceType,
            'actor_id' => $this->actorId,
            'error' => $exception->getMessage(),
        ]);
    }
}
