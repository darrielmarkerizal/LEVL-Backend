<?php

declare(strict_types=1);

namespace Modules\Trash\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Trash\Models\TrashBin;
use Modules\Trash\Services\TrashBinService;

class RestoreTrashBinJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 900;

    public function __construct(
        public int $trashBinId,
        public ?int $actorId = null,
    ) {
        $this->onQueue('schemes');
    }

    public function handle(TrashBinService $trashBinService): void
    {
        $bin = TrashBin::query()->find($this->trashBinId);

        if (! $bin) {
            Log::info('RestoreTrashBinJob: trash bin not found, skipping', [
                'trash_bin_id' => $this->trashBinId,
                'actor_id' => $this->actorId,
            ]);

            return;
        }

        $trashBinService->restoreFromTrashBin($bin);
    }
}
