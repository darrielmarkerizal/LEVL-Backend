<?php

declare(strict_types=1);

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Gamification\Services\BadgeVersionService;

class CreateInitialBadgeVersions extends Command
{
    protected $signature = 'gamification:create-initial-versions';

    protected $description = 'Create initial versions for existing badges';

    public function handle(BadgeVersionService $versionService): int
    {
        $this->info(__('gamification::gamification.creating_versions'));

        $count = $versionService->createInitialVersionsForExistingBadges();

        $this->info("✅ " . __('gamification::gamification.initial_versions_created', ['count' => $count]));

        return 0;
    }
}
