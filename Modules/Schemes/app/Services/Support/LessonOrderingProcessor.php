<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Schemes\Contracts\Repositories\LessonRepositoryInterface;
use Modules\Schemes\DTOs\CreateLessonDTO;
use Modules\Schemes\DTOs\UpdateLessonDTO;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Services\SchemesCacheService;
use Modules\Schemes\Services\UnitContentSyncService;

class LessonOrderingProcessor
{
    public function __construct(
        private readonly LessonRepositoryInterface $repository,
        private readonly SchemesCacheService $cacheService,
        private readonly UnitContentSyncService $syncService
    ) {}

    public function create(int $unitId, CreateLessonDTO|array $data): Lesson
    {
        return DB::transaction(function () use ($unitId, $data) {
            $attributes = $data instanceof CreateLessonDTO ? $data->toArrayWithoutNull() : $data;
            $attributes['unit_id'] = $unitId;

            if (! isset($attributes['order'])) {
                $attributes['order'] = $this->syncService->getNextOrder($unitId);
            }

            $attributes = Arr::except($attributes, ['slug']);

            $lesson = $this->repository->create($attributes);

            $this->syncService->register('lesson', $lesson->id, $unitId, $attributes['order']);

            return $lesson;
        });
    }

    public function update(Lesson $lesson, UpdateLessonDTO|array $data): Lesson
    {
        return DB::transaction(function () use ($lesson, $data) {
            $attributes = $data instanceof UpdateLessonDTO ? $data->toArrayWithoutNull() : $data;

            if (isset($attributes['order']) && $attributes['order'] != $lesson->order) {
                $newOrder = $attributes['order'];
                $currentOrder = $lesson->order;
                $unitId = $lesson->unit_id;

                if ($newOrder < $currentOrder) {
                    Lesson::where('unit_id', $unitId)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $currentOrder)
                        ->increment('order');
                } elseif ($newOrder > $currentOrder) {
                    Lesson::where('unit_id', $unitId)
                        ->where('order', '>', $currentOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                }
            }

            $attributes = Arr::except($attributes, ['slug']);

            return $this->repository->update($lesson, $attributes);
        });
    }

    public function delete(Lesson $lesson): bool
    {
        return DB::transaction(function () use ($lesson) {
            $deleted = $this->repository->delete($lesson);

            if ($deleted) {
                $this->syncService->unregister('lesson', $lesson->id);
            }

            return $deleted;
        });
    }

    public function validateHierarchy(int $courseId, int $unitId, ?int $lessonId = null): void
    {
        if ($lessonId) {
            $lesson = Lesson::with('unit')->findOrFail($lessonId);
            if ((int) $lesson->unit?->course_id !== $courseId || (int) $lesson->unit_id !== $unitId) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(__('messages.lessons.not_found'));
            }
        } else {
            $unit = Unit::findOrFail($unitId);
            if ((int) $unit->course_id !== $courseId) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(__('messages.units.not_in_course'));
            }
        }
    }

    public function publish(Lesson $lesson): Lesson
    {
        $lesson->update(['status' => 'published']);

        $courseId = $lesson->unit->course_id;
        $this->cacheService->invalidateCourse($courseId);

        return $lesson->fresh();
    }

    public function unpublish(Lesson $lesson): Lesson
    {
        $lesson->update(['status' => 'draft']);

        $courseId = $lesson->unit->course_id;
        $this->cacheService->invalidateCourse($courseId);

        return $lesson->fresh();
    }
}
