<?php

namespace Modules\Assessments\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Modules\Assessments\Models\Exercise;
use Modules\Assessments\Repositories\ExerciseRepository;

class ExerciseService
{
    public function __construct(private readonly ExerciseRepository $repository) {}

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(array $data, int $creatorId): Exercise
    {
        $payload = $data;
        $payload['created_by'] = $creatorId;
        $payload['status'] = $payload['status'] ?? 'draft';
        $payload['total_questions'] = $payload['total_questions'] ?? 0;

        return $this->repository->create($payload);
    }

    public function update(Exercise $exercise, array $data): Exercise
    {
        return $this->repository->update($exercise, $data);
    }

    public function delete(Exercise $exercise): bool
    {
        return $this->repository->delete($exercise);
    }

    /**
     * @throws ValidationException
     */
    public function publish(Exercise $exercise): Exercise
    {
        if ($this->repository->questionCount($exercise) === 0) {
            throw ValidationException::withMessages([
                'questions' => ['Soal wajib memiliki minimal satu pertanyaan sebelum dipublikasikan.'],
            ]);
        }

        return $this->repository->update($exercise, ['status' => 'published']);
    }

    public function questions(Exercise $exercise): Collection
    {
        return $this->repository->questionsWithOptions($exercise);
    }
}
