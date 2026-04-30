<?php

declare(strict_types=1);

namespace Modules\Gamification\Console;

use Illuminate\Console\Command;
use Modules\Auth\Models\User;
use Modules\Gamification\Jobs\EvaluateRetroactiveBadgesJob;
use Modules\Gamification\Models\BadgeRule;

class RetroactiveBadgeCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamification:check-retroactive 
                            {--event= : Filter by specific event trigger (e.g. quiz_passed)} 
                            {--badge= : Filter by specific badge ID} 
                            {--user= : Filter by specific user ID}
                            {--force : Force execution without asking for confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retroactively evaluate and assign gamification badges to eligible users.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $eventId = $this->option('event');
        $badgeId = $this->option('badge') ? (int) $this->option('badge') : null;
        $userId = $this->option('user') ? (int) $this->option('user') : null;

        if (! $eventId && ! $badgeId && ! $userId && ! $this->option('force')) {
            $this->warn('You are about to evaluate ALL rules for ALL users.');
            $this->warn('This process will dispatch thousands of background jobs and might take a long time.');
            if (! $this->confirm('Do you wish to continue?')) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        // Validate badge if provided
        if ($badgeId) {
            $ruleExists = BadgeRule::where('badge_id', $badgeId)->exists();
            if (! $ruleExists) {
                $this->error("No active BadgeRule found for badge ID {$badgeId}.");
                return self::FAILURE;
            }
        }

        $query = User::query()->where('status', 'active');

        // Focus mainly on roles that usually receive gamification points
        // Assuming students/asesi are the primary audience
        $query->whereHas('roles', function ($q) {
            $q->whereIn('name', ['Student', 'Asesi']);
        });

        if ($userId) {
            $query->where('id', $userId);
        }

        $totalUsers = $query->count();
        if ($totalUsers === 0) {
            $this->info('No eligible active users found to evaluate.');
            return self::SUCCESS;
        }

        $this->info("Starting retroactive badge evaluation for {$totalUsers} user(s).");
        
        $bar = $this->output->createProgressBar($totalUsers);
        $dispatchedCount = 0;

        $query->chunk(500, function ($users) use ($eventId, $badgeId, $bar, &$dispatchedCount) {
            foreach ($users as $user) {
                EvaluateRetroactiveBadgesJob::dispatch(
                    $user->id,
                    $eventId,
                    $badgeId
                );
                
                $dispatchedCount++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        
        $this->info("Successfully dispatched {$dispatchedCount} evaluation jobs to the queue.");
        return self::SUCCESS;
    }
}
