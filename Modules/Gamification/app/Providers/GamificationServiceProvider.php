<?php

namespace Modules\Gamification\Providers;

use App\Support\Traits\RegistersModuleConfig;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class GamificationServiceProvider extends ServiceProvider
{
    use PathNamespace, RegistersModuleConfig;

    protected string $name = 'Gamification';

    protected string $nameLower = 'gamification';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerPolicies();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    protected function registerPolicies(): void
    {
        \Illuminate\Support\Facades\Gate::policy(
            \Modules\Gamification\Models\Badge::class,
            \Modules\Gamification\Policies\BadgePolicy::class
        );
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Existing repositories
        $this->app->bind(
            \Modules\Gamification\Contracts\Repositories\GamificationRepositoryInterface::class,
            \Modules\Gamification\Repositories\GamificationRepository::class
        );
        $this->app->bind(
            \Modules\Gamification\Contracts\Repositories\UserGamificationStatRepositoryInterface::class,
            \Modules\Gamification\Repositories\UserGamificationStatRepository::class
        );
        $this->app->bind(
            \Modules\Gamification\Contracts\Repositories\UserBadgeRepositoryInterface::class,
            \Modules\Gamification\Repositories\UserBadgeRepository::class
        );
        $this->app->bind(
            \Modules\Gamification\Contracts\Repositories\PointRepositoryInterface::class,
            \Modules\Gamification\Repositories\PointRepository::class
        );
        $this->app->bind(
            \Modules\Gamification\Contracts\Repositories\BadgeRepositoryInterface::class,
            \Modules\Gamification\Repositories\BadgeRepository::class
        );

        // New repositories for production-grade system
        $this->app->bind(
            \Modules\Gamification\Contracts\Repositories\UserEventCounterRepositoryInterface::class,
            \Modules\Gamification\Repositories\UserEventCounterRepository::class
        );
        $this->app->bind(
            \Modules\Gamification\Contracts\Repositories\GamificationEventLogRepositoryInterface::class,
            \Modules\Gamification\Repositories\GamificationEventLogRepository::class
        );
        $this->app->bind(
            \Modules\Gamification\Contracts\Repositories\BadgeVersionRepositoryInterface::class,
            \Modules\Gamification\Repositories\BadgeVersionRepository::class
        );

        // Services
        $this->app->singleton(
            \Modules\Gamification\Contracts\Services\GamificationServiceInterface::class,
            \Modules\Gamification\Services\GamificationService::class
        );
        $this->app->singleton(
            \Modules\Gamification\Contracts\Services\LeaderboardServiceInterface::class,
            \Modules\Gamification\Services\LeaderboardService::class
        );
        $this->app->singleton(
            \Modules\Gamification\Contracts\Services\BadgeServiceInterface::class,
            \Modules\Gamification\Services\BadgeService::class
        );

        $this->app->singleton(
            \Modules\Gamification\Services\Support\StreakResetService::class
        );
    }

    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\Gamification\Console\Commands\UpdateLeaderboard::class,
            \Modules\Gamification\Console\Commands\ResetInactiveStreaks::class,
            \Modules\Gamification\Console\Commands\WarmBadgeRulesCache::class,
            \Modules\Gamification\Console\Commands\CleanupOldEventLogs::class,
            \Modules\Gamification\Console\Commands\CleanupExpiredCounters::class,
            \Modules\Gamification\Console\Commands\CreateInitialBadgeVersions::class,
            \Modules\Gamification\Console\Commands\SyncLevelConfigs::class,
            \Modules\Gamification\Console\Commands\SeedGamificationData::class,
        ]);
    }

    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $schedule->command('streaks:reset-inactive')->dailyAt('00:00')->timezone('Asia/Jakarta');
            $schedule->command('leaderboard:update')->everyFiveMinutes();

            // Production-grade cleanup commands
            $schedule->command('gamification:cleanup-logs --days=90')->daily()->at('02:00');
            $schedule->command('gamification:cleanup-counters')->daily()->at('03:00');
            $schedule->command('gamification:warm-cache')->daily()->at('04:00');
        });
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    protected function registerConfig(): void
    {
        $this->registerModuleConfig();
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\'.$this->name.'\\View\\Components', $this->nameLower);
    }

    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
