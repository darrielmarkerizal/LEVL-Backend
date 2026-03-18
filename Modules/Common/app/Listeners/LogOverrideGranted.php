<?php

declare(strict_types=1);

namespace Modules\Common\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Contracts\Services\AuditServiceInterface;
use Modules\Learning\Events\OverrideGranted;

class LogOverrideGranted implements ShouldQueue
{
    use \Illuminate\Queue\InteractsWithQueue;

    public string $queue = 'audit';

    public int $tries = 3;

    public int $maxExceptions = 2;

    public array $backoff = [5, 30, 120];

    public function __construct(
        private readonly AuditServiceInterface $auditService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OverrideGranted $event): void
    {
        $override = $event->override;

        $this->auditService->logOverrideGrant(
            $override->assignment_id,
            $override->student_id,
            $override->type->value,
            $override->reason,
            $event->instructorId
        );
    }
}
