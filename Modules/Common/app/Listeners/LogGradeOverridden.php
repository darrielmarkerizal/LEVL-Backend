<?php

declare(strict_types=1);

namespace Modules\Common\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Contracts\Services\AuditServiceInterface;
use Modules\Grading\Events\GradeOverridden;

class LogGradeOverridden implements ShouldQueue
{
    use \Illuminate\Queue\InteractsWithQueue;

    public string $queue = 'audit';

    public int $tries = 3;

    public int $maxExceptions = 2;

    public array $backoff = [5, 30, 120];

    public function __construct(
        private readonly AuditServiceInterface $auditService
    ) {}

    public function handle(GradeOverridden $event): void
    {
        $this->auditService->logGradeOverride(
            $event->grade,
            $event->oldScore,
            $event->newScore,
            $event->reason,
            $event->instructorId
        );
    }
}
