<?php

declare(strict_types=1);

namespace Modules\Trash\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Trash\Services\TrashBinService;

class RestoreAllTrashBinsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 1800;

    public function __construct(
        public ?string $resourceType = null,
        public ?int $actorId = null,
    ) {
        $this->onQueue('schemes');
    }

    public function handle(TrashBinService $trashBinService): void
    {
        $trashBinService->restoreAll($this->resourceType);
    }
}
