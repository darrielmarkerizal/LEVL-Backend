<?php

declare(strict_types=1);

namespace Modules\Learning\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Learning\Models\Quiz;

interface QuizServiceInterface
{
    public function resolveCourseFromScope(string $assignableType, int $assignableId): ?\Modules\Schemes\Models\Course;

    public function resolveCourseFromScopeOrFail(?array $scope): \Modules\Schemes\Models\Course;

    public function list(\Modules\Schemes\Models\Course $course, array $filters = []): LengthAwarePaginator;

    public function listForIndex(\Modules\Schemes\Models\Course $course, array $filters = []): LengthAwarePaginator;

    public function create(array $data, int $createdBy): Quiz;

    public function update(Quiz $quiz, array $data): Quiz;

    public function delete(Quiz $quiz): bool;

    public function publish(Quiz $quiz): Quiz;

    public function unpublish(Quiz $quiz): Quiz;

    public function archive(Quiz $quiz): Quiz;

    public function getWithRelations(Quiz $quiz): Quiz;
}
