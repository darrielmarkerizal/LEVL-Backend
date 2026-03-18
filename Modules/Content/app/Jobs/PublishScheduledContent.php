<?php

declare(strict_types=1);

namespace Modules\Content\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Content\Services\ContentSchedulingService;
use Throwable;

class PublishScheduledContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public array $backoff = [5, 30, 120];

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(ContentSchedulingService $schedulingService): void
    {
        $publishedCount = $schedulingService->publishScheduledContent();

        Log::info("PublishScheduledContent job completed. Published {$publishedCount} items.");
    }

    public function failed(Throwable $exception): void
    {
        Log::error('PublishScheduledContent job failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
