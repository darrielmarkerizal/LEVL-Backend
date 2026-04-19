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
            
            $routeName = $route->getName();
            if ($routeName && str_starts_with($routeName, 'api.quiz-submissions')) {
                
                if (is_numeric($value)) {
                    return \Modules\Learning\Models\QuizSubmission::findOrFail($value);
                }

                
                if (str_contains($value, ':')) {
                    abort(404, 'Invalid quiz submission identifier');
                }

                return \Modules\Learning\Models\QuizSubmission::findOrFail($value);
            }

            
            if ($value === 'me') {
                $assignment = $route->parameter('assignment');
                if (! $assignment) {
                    abort(404);
                }

                $assignmentId = is_object($assignment) ? $assignment->id : (is_numeric($assignment) ? (int) $assignment : null);
                if (! $assignmentId) {
                    abort(404);
                }

                $userId = auth('api')->id();
                if (! $userId) {
                    abort(401);
                }

                $submission = \Modules\Learning\Models\Submission::where('assignment_id', $assignmentId)
                    ->where('user_id', $userId)
                    ->orderByDesc('score')
                    ->orderByDesc('submitted_at')
                    ->first();

                if (! $submission) {
                    abort(404, __('messages.submissions.not_found'));
                }

                return $submission;
            }

            
            if (is_numeric($value)) {
                return \Modules\Learning\Models\Submission::findOrFail($value);
            }

            
            if (str_contains($value, ':')) {
                abort(404, 'Invalid submission identifier');
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
