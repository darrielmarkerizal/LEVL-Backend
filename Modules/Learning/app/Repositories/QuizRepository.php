<?php

declare(strict_types=1);

namespace Modules\Learning\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Learning\Contracts\Repositories\QuizRepositoryInterface;
use Modules\Learning\Models\Quiz;

class QuizRepository extends BaseRepository implements QuizRepositoryInterface
{
    protected function model(): string
    {
        return Quiz::class;
    }

    public function create(array $attributes): Quiz
    {
        return Quiz::create($attributes);
    }

    public function update(\Illuminate\Database\Eloquent\Model $model, array $data): Quiz
    {
        assert($model instanceof Quiz);
        $model->fill($data)->save();

        return $model;
    }

    public function delete(\Illuminate\Database\Eloquent\Model $model): bool
    {
        assert($model instanceof Quiz);

        return $model->delete();
    }

    public function findWithRelations(Quiz $quiz): Quiz
    {
        return $quiz->loadMissing([
            'creator:id,name,email',
            'unit:id,slug,title,code,course_id',
            'unit.course:id,slug,title,code',
            'questions',
        ]);
    }

    public function paginate(array $params = [], int $perPage = 15): LengthAwarePaginator
    {
        return Quiz::query()->orderByDesc('created_at')->paginate($perPage);
    }

    public function listByCourse(int $courseId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Quiz::query()
            ->forCourse($courseId)
            ->with(['creator:id,name,email', 'questions'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function listByUnit(int $unitId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Quiz::query()
            ->forUnit($unitId)
            ->with(['creator:id,name,email', 'questions'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function listByLesson(int $lessonId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        $lesson = \Modules\Schemes\Models\Lesson::find($lessonId);
        if (! $lesson) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return Quiz::query()
            ->where('unit_id', $lesson->unit_id)
            ->with(['creator:id,name,email', 'questions'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($filters);
    }
}
