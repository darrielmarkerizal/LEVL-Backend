<?php

declare(strict_types=1);

namespace Modules\Schemes\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Schemes\Services\UnitService;

class DeleteUnitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 900;

    public function __construct(
        public int $unitId,
        public ?int $actorId = null,
    ) {
        $this->onQueue('schemes');
    }

    public function handle(UnitService $unitService): void
    {
        try {
            $unitService->delete($this->unitId);
        } catch (ModelNotFoundException) {
            Log::info('DeleteUnitJob: unit already deleted, skipping', [
                'unit_id' => $this->unitId,
                'actor_id' => $this->actorId,
            ]);
        }
    }
}
