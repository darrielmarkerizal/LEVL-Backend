<?php

declare(strict_types=1);

namespace Modules\Gamification\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\Gamification\Models\BadgeRule;

class WarmBadgeRulesCache extends Command
{
    protected $signature = 'gamification:warm-cache';

    protected $description = 'Warm badge rules cache indexed by event';

    public function handle(): int
    {
        $this->info(__('gamification::gamification.cache_warming'));

        $events = BadgeRule::distinct('event_trigger')
            ->whereNotNull('event_trigger')
            ->pluck('event_trigger');

        $count = 0;

        foreach ($events as $event) {
            Cache::tags(['gamification', 'rules'])->remember(
                "gamification.rules_by_event.{$event}",
                3600,
                fn () => BadgeRule::with('badge')
                    ->where('event_trigger', $event)
                    ->orderBy('priority', 'desc')
                    ->get()
            );

            $this->info('✓ '.__('gamification::gamification.cached_event', ['event' => $event]));
            $count++;
        }

        $this->info('✅ '.__('gamification::gamification.cache_warmed', ['count' => $count]));

        return 0;
    }
}
