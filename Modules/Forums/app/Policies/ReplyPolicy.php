<?php

namespace Modules\Forums\Policies;

use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;

class ReplyPolicy
{
     
    public function view(User $user, Reply $reply): bool
    {
        if ($user->hasRole(['Admin', 'Superadmin'])) {
            return true;
        }

        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $reply->thread->course_id)
            ->exists();
    }

     
    public function create(User $user, Thread $thread): bool
    {
        if ($user->hasRole(['Admin', 'Superadmin'])) {
            return true;
        }

        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $thread->course_id)
            ->exists();
    }

     
    public function update(User $user, Reply $reply): bool
    {
        if ($user->hasRole(['Admin', 'Superadmin'])) {
            return true;
        }

        return $user->id === $reply->author_id;
    }

     
    public function delete(User $user, Reply $reply): bool
    {
        
        return $user->id === $reply->author_id || $this->isModerator($user, $reply->thread->course_id);
    }

     
    public function markAsAccepted(User $user, Reply $reply): bool
    {
        return $this->isInstructor($user, $reply->thread->course_id);
    }

     
    protected function isModerator(User $user, int $courseId): bool
    {
        if ($user->hasRole(['Admin', 'Superadmin'])) {
            return true;
        }

        if ($user->hasRole('instructor')) {
            return \Modules\Schemes\Models\Course::where('id', $courseId)
                ->where('instructor_id', $user->id)
                ->exists();
        }

        return false;
    }

    protected function isInstructor(User $user, int $courseId): bool
    {
        if ($user->hasRole(['Admin', 'Superadmin'])) {
            return true;
        }

        if ($user->hasRole('instructor')) {
            return \Modules\Schemes\Models\Course::where('id', $courseId)
                ->where('instructor_id', $user->id)
                ->exists();
        }

        return false;
    }
}
