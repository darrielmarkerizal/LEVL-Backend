<?php

declare(strict_types=1);

namespace Modules\Trash\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Trash\Services\TrashBinService;

class BulkForceDeleteTrashBinsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

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

        $trashBinService->forceDeleteMany($this->ids);
    }
}
