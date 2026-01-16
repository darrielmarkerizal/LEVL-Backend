<?php

declare(strict_types=1);

namespace Modules\Schemes\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Schemes\Models\Unit;

class UnitCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Unit $unit,
        public int $userId,
        public int $enrollmentId
    ) {}
}
