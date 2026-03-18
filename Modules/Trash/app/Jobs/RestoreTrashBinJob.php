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
use Throwable;

class RestoreTrashBinJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public int $timeout = 300;

    public array $backoff = [10, 60, 300];

    public function __construct(
        public int $trashBinId,
        public ?int $actorId = null,
    ) {
        $this->onQueue('trash');
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

    public function failed(Throwable $exception): void
    {
        Log::error('RestoreTrashBinJob failed', [
            'trash_bin_id' => $this->trashBinId,
            'actor_id' => $this->actorId,
            'error' => $exception->getMessage(),
        ]);
    }
}
