<?php

declare(strict_types=1);

namespace Modules\Enrollments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class InitializeEnrollmentProgressJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $enrollmentId,
        private readonly int $courseId,
    ) {}

    public function handle(): void
    {
        $now = now()->toDateTimeString();

        DB::transaction(function () use ($now) {
            $this->insertUnitProgress($now);
            $this->insertLessonProgress($now);
            $this->insertCourseProgress($now);
        });
    }

    private function insertUnitProgress(string $now): void
    {
        $units = DB::table('units')
            ->select('id')
            ->where('course_id', $this->courseId)
            ->whereNull('deleted_at')
            ->get();

        if ($units->isEmpty()) {
            return;
        }

        $records = $units->map(function ($unit) use ($now) {
            return [
                'enrollment_id' => $this->enrollmentId,
                'unit_id' => $unit->id,
                'status' => 'not_started',
                'progress_percent' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        foreach (array_chunk($records, 1000) as $chunk) {
            DB::table('unit_progress')->insertOrIgnore($chunk);
        }
    }

    private function insertLessonProgress(string $now): void
    {
        $lessons = DB::table('lessons')
            ->select('lessons.id')
            ->join('units', 'lessons.unit_id', '=', 'units.id')
            ->where('units.course_id', $this->courseId)
            ->where('lessons.status', 'published')
            ->whereNull('lessons.deleted_at')
            ->whereNull('units.deleted_at')
            ->get();

        if ($lessons->isEmpty()) {
            return;
        }

        $records = $lessons->map(function ($lesson) use ($now) {
            return [
                'enrollment_id' => $this->enrollmentId,
                'lesson_id' => $lesson->id,
                'status' => 'not_started',
                'progress_percent' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        foreach (array_chunk($records, 1000) as $chunk) {
            DB::table('lesson_progress')->insertOrIgnore($chunk);
        }
    }

    private function insertCourseProgress(string $now): void
    {
        DB::table('course_progress')->insertOrIgnore([
            'enrollment_id' => $this->enrollmentId,
            'status' => 'not_started',
            'progress_percent' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
