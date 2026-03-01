<?php

declare(strict_types=1);

namespace Modules\Learning\Policies;

use Modules\Auth\Models\User;
use Modules\Learning\Models\QuizSubmission;

class QuizSubmissionPolicy
{
    public function view(?User $user, QuizSubmission $submission): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Superadmin') || $user->hasRole('Admin') || $user->hasRole('Instructor')) {
            return true;
        }

        return $submission->user_id === $user->id;
    }

    public function update(User $user, QuizSubmission $submission): bool
    {
        if ($submission->user_id !== $user->id) {
            return false;
        }

        return $submission->status?->value === 'draft';
    }
}
