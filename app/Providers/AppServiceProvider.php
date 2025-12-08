<?php

namespace App\Providers;

use App\Contracts\EnrollmentKeyHasherInterface;
use App\Support\EnrollmentKeyHasher;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            EnrollmentKeyHasherInterface::class,
            EnrollmentKeyHasher::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureScramble();

        if ($this->app->environment('local')) {
            Mail::alwaysTo(config('mail.development_to', 'dev@local.test'));
        }
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Default API rate limiter
        RateLimiter::for('api', function (Request $request) {
            $config = config('rate-limiting.api.default');

            return Limit::perMinutes($config['decay'], $config['max'])
                ->by($request->user()?->id ?: $request->ip());
        });

        // Auth endpoints rate limiter (more restrictive)
        RateLimiter::for('auth', function (Request $request) {
            $config = config('rate-limiting.api.auth');

            return Limit::perMinutes($config['decay'], $config['max'])
                ->by($request->ip());
        });

        // Enrollment endpoints rate limiter (most restrictive)
        RateLimiter::for('enrollment', function (Request $request) {
            $config = config('rate-limiting.api.enrollment');

            return Limit::perMinutes($config['decay'], $config['max'])
                ->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Configure Scramble for OpenAPI documentation.
     */
    protected function configureScramble(): void
    {
        // Register all API routes (including module routes)
        Scramble::configure()
            ->routes(function (Route $route) {
                return Str::startsWith($route->uri, 'api/');
            });

        // Configure OpenAPI info and security after generation
        Scramble::configure()
            ->afterOpenApiGenerated(function (OpenApi $openApi) {
                $openApi->info->title = config('app.name', 'TA Prep LSP').' API';

                // Add JWT Bearer authentication
                $openApi->secure(
                    SecurityScheme::http('bearer', 'JWT')
                );
            });
    }
}
