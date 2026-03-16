<?php

declare(strict_types=1);

namespace Modules\Schemes\Jobs;

use App\Jobs\LogActivityJob;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Schemes\Contracts\Services\CourseServiceInterface;
use Modules\Schemes\Models\Course;

class DeleteCourseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 900;

    public function __construct(
        public int $courseId,
        public ?int $actorId = null
    ) {
        $this->onQueue('schemes');
    }

    public function handle(CourseServiceInterface $courseService): void
    {
        $course = Course::query()->find($this->courseId);

        if (! $course) {
            Log::info('DeleteCourseJob: course not found, skipping', [
                'course_id' => $this->courseId,
                'actor_id' => $this->actorId,
            ]);

            return;
        }

        $title = (string) $course->title;

        // Set deleted_by before deleting so TrashBin can capture it
        if ($this->actorId) {
            $course->deleted_by = $this->actorId;
            $course->saveQuietly(); // Save without triggering events
        }

        try {
            $courseService->delete($this->courseId);
        } catch (ModelNotFoundException) {
            Log::info('DeleteCourseJob: course already deleted, skipping', [
                'course_id' => $this->courseId,
                'actor_id' => $this->actorId,
            ]);

            return;
        }

        if ($this->actorId) {
            dispatch(new LogActivityJob([
                'log_name' => 'schemes',
                'causer_id' => $this->actorId,
                'description' => "Deleted course: {$title}",
                'properties' => [
                    'course_id' => $this->courseId,
                    'action' => 'delete',
                    'mode' => 'async',
                ],
            ]));
        }
    }
}
