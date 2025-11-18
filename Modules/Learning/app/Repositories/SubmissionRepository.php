<?php

namespace Modules\Learning\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Models\User;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;

class SubmissionRepository
{
    public function listForAssignment(Assignment $assignment, User $user, array $filters = []): Collection
    {
        $query = Submission::query()
            ->where('assignment_id', $assignment->id)
            ->with(['user:id,name,email', 'enrollment:id,status', 'files', 'grade']);

        if ($user->hasRole('Student')) {
            $query->where('user_id', $user->id);
        } elseif (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function create(array $attributes): Submission
    {
        return Submission::create($attributes);
    }

    public function update(Submission $submission, array $attributes): Submission
    {
        $submission->fill($attributes)->save();

        return $submission;
    }

    public function delete(Submission $submission): bool
    {
        return $submission->delete();
    }

    public function latestCommittedSubmission(Assignment $assignment, int $userId): ?Submission
    {
        return Submission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['submitted', 'late', 'graded'])
            ->latest('id')
            ->first();
    }
}
