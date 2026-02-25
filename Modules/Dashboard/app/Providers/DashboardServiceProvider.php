<?php

declare(strict_types=1);

namespace Modules\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    protected string $name = 'Dashboard';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        $this->app->bind(
            \Modules\Dashboard\Contracts\Repositories\DashboardRepositoryInterface::class,
            \Modules\Dashboard\Repositories\DashboardRepository::class
        );

        $this->app->bind(
            \Modules\Dashboard\Contracts\Services\DashboardServiceInterface::class,
            \Modules\Dashboard\Services\DashboardService::class
        );
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->name, 'config/config.php'), 'dashboard'
        );
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/dashboard');
        $sourcePath = module_path($this->name, 'resources/views');

        $this->loadViewsFrom(array_merge([$viewPath], [$sourcePath]), 'dashboard');
    }
}
