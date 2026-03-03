<?php

declare(strict_types=1);

namespace Modules\Learning\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Learning';

    public function boot(): void
    {
        parent::boot();

        Route::bind('submission', function (string $value, \Illuminate\Routing\Route $route) {
            if ($value === 'me') {
                $assignment = $route->parameter('assignment');
                if (! $assignment) {
                    abort(404);
                }

                $userId = auth('api')->id();
                if (! $userId) {
                    abort(401);
                }

                $submission = \Modules\Learning\Models\Submission::where('assignment_id', $assignment->id)
                    ->where('user_id', $userId)
                    ->orderByDesc('score')
                    ->orderByDesc('submitted_at')
                    ->first();

                if (! $submission) {
                    abort(404, __('messages.submissions.not_found'));
                }

                return $submission;
            }

            return \Modules\Learning\Models\Submission::findOrFail($value);
        });
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware('api')->prefix('api')->name('api.')->group(module_path($this->name, '/routes/api.php'));
    }
}
