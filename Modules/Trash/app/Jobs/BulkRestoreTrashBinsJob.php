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

class BulkRestoreTrashBinsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public int $timeout = 600;

    public array $backoff = [10, 60, 300];

    public function __construct(
        public array $ids,
        public ?int $actorId = null,
    ) {
        $this->onQueue('trash');
    }

    public function handle(TrashBinService $trashBinService): void
    {
        if ($this->ids === []) {
            return;
        }

        $trashBinService->restoreMany($this->ids);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('BulkRestoreTrashBinsJob failed', [
            'ids_count' => count($this->ids),
            'actor_id' => $this->actorId,
            'error' => $exception->getMessage(),
        ]);
    }
}
