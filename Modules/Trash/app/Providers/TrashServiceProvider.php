<?php

declare(strict_types=1);

namespace Modules\Trash\Providers;

use App\Support\Traits\RegistersModuleConfig;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class TrashServiceProvider extends ServiceProvider
{
    use PathNamespace, RegistersModuleConfig;

    protected string $name = 'Trash';

    protected string $nameLower = 'trash';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
        
        // Register cache invalidation for Octane
        $this->registerOctaneCacheInvalidation();
    }
    
    /**
     * Register cache invalidation when trash bins are modified
     */
    protected function registerOctaneCacheInvalidation(): void
    {
        // Listen to trash bin events to invalidate cache
        \Illuminate\Support\Facades\Event::listen(
            [\Modules\Trash\Models\TrashBin::class . '::created', \Modules\Trash\Models\TrashBin::class . '::deleted'],
            function () {
                \Illuminate\Support\Facades\Cache::forget('trash_bins:source_types');
            }
        );
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        $this->app->scoped(
            \Modules\Trash\Services\TrashDeleteContext::class,
        );

        $this->app->bind(
            \Modules\Trash\Contracts\Repositories\TrashBinRepositoryInterface::class,
            \Modules\Trash\Repositories\TrashBinRepository::class
        );

        $this->app->bind(
            \Modules\Trash\Contracts\Services\TrashBinManagementServiceInterface::class,
            \Modules\Trash\Services\TrashBinManagementService::class
        );
    }

    protected function registerCommands(): void {}

    protected function registerCommandSchedules(): void {}

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
