<?php

namespace Modules\Content\Providers;

use App\Support\Traits\RegistersModuleConfig;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class ContentServiceProvider extends ServiceProvider
{
    use PathNamespace, RegistersModuleConfig;

    protected string $name = 'Content';

    protected string $nameLower = 'content';

    
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
        \Illuminate\Support\Facades\Gate::policy(\Modules\Content\Models\Announcement::class, \Modules\Content\Policies\AnnouncementPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Modules\Content\Models\News::class, \Modules\Content\Policies\NewsPolicy::class);
    }

    
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->registerBindings();
    }

    
    protected function registerBindings(): void
    {
        
        $this->app->bind(
            \Modules\Content\Contracts\Repositories\AnnouncementRepositoryInterface::class,
            \Modules\Content\Repositories\AnnouncementRepository::class
        );
        $this->app->bind(
            \Modules\Content\Contracts\Repositories\NewsRepositoryInterface::class,
            \Modules\Content\Repositories\NewsRepository::class
        );

        
        $this->app->bind(
            \Modules\Content\Contracts\Services\AnnouncementServiceInterface::class,
            \Modules\Content\Services\AnnouncementService::class
        );
        $this->app->bind(
            \Modules\Content\Contracts\Services\NewsServiceInterface::class,
            \Modules\Content\Services\NewsService::class
        );
        $this->app->bind(
            \Modules\Content\Contracts\Services\ContentStatisticsServiceInterface::class,
            \Modules\Content\Services\ContentStatisticsService::class
        );

        
        $this->app->bind(
            \App\Contracts\Services\ContentServiceInterface::class,
            \Modules\Content\Services\ContentService::class
        );
        $this->app->bind(
            \Modules\Content\Contracts\Services\ContentWorkflowServiceInterface::class,
            \Modules\Content\Services\ContentWorkflowService::class
        );
    }

    
    protected function registerCommands(): void
    {
        
    }

    
    protected function registerCommandSchedules(): void
    {
        
        
        
        
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
