<?php

declare(strict_types=1);

namespace Modules\Learning\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Learning\Models\Quiz;

interface QuizRepositoryInterface
{
    public function create(array $data): Quiz;

    public function update(\Illuminate\Database\Eloquent\Model $model, array $data): Quiz;

    public function delete(\Illuminate\Database\Eloquent\Model $model): bool;

    public function findWithRelations(Quiz $quiz): Quiz;

    public function paginate(array $params = [], int $perPage = 15): LengthAwarePaginator;

    public function listByCourse(int $courseId, array $filters = []): LengthAwarePaginator;

    public function listByUnit(int $unitId, array $filters = []): LengthAwarePaginator;

    public function listByLesson(int $lessonId, array $filters = []): LengthAwarePaginator;
}
