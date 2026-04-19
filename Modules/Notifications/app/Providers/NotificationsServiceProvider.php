<?php

namespace Modules\Notifications\Providers;

use App\Support\Traits\RegistersModuleConfig;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class NotificationsServiceProvider extends ServiceProvider
{
    use PathNamespace, RegistersModuleConfig;

    protected string $name = 'Notifications';

    protected string $nameLower = 'notifications';

    
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
        Gate::policy(\Modules\Notifications\Models\Post::class, \Modules\Notifications\Policies\PostPolicy::class);
    }

    
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        
        $this->app->bind(
            \Modules\Notifications\Contracts\Services\NotificationPreferenceServiceInterface::class,
            \Modules\Notifications\Services\NotificationPreferenceService::class
        );

        $this->app->bind(
            \Modules\Notifications\Contracts\Services\GradingNotificationServiceInterface::class,
            \Modules\Notifications\Services\GradingNotificationService::class
        );
    }

    
    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\Notifications\Console\Commands\PublishScheduledPostsCommand::class,
            \Modules\Notifications\Console\Commands\CleanupOrphanedMediaCommand::class,
        ]);
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
