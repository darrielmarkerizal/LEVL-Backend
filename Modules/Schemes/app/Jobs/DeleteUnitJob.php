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
use Throwable;

class DeleteUnitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public int $timeout = 900;

    public array $backoff = [5, 30, 120];

    public function __construct(
        public int $unitId,
        public ?int $actorId = null,
    ) {
        $this->onQueue('trash');
    }

    public function handle(UnitService $unitService): void
    {
        if ($this->actorId) {
            $user = \Modules\Auth\Models\User::find($this->actorId);

            if ($user) {
                auth()->setUser($user);
                auth('api')->setUser($user);
            }
        }

        try {
            $unitService->delete($this->unitId);
        } catch (ModelNotFoundException) {
            Log::info('DeleteUnitJob: unit already deleted, skipping', [
                'unit_id' => $this->unitId,
                'actor_id' => $this->actorId,
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('DeleteUnitJob failed', [
            'unit_id' => $this->unitId,
            'actor_id' => $this->actorId,
            'error' => $exception->getMessage(),
        ]);
    }
}
