<?php

namespace Modules\Forums\Policies;

use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Forums\Models\Thread;

class ThreadPolicy
{
     
    public function view(User $user, Thread $thread): bool
    {
        
        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $thread->scheme_id)
            ->exists();
    }

     
    public function create(User $user, int $schemeId): bool
    {
        
        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $schemeId)
            ->exists();
    }

     
    public function update(User $user, Thread $thread): bool
    {
        
        return $user->id === $thread->author_id;
    }

     
    public function delete(User $user, Thread $thread): bool
    {
        
        return $user->id === $thread->author_id || $this->isModerator($user, $thread->scheme_id);
    }

     
    public function pin(User $user, Thread $thread): bool
    {
        return $this->isModerator($user, $thread->scheme_id);
    }

     
    public function close(User $user, Thread $thread): bool
    {
        return $this->isModerator($user, $thread->scheme_id);
    }

     
    protected function isModerator(User $user, int $schemeId): bool
    {
        
        if ($user->hasRole('admin')) {
            return true;
        }

        
        if ($user->hasRole('instructor')) {
            
            return \Modules\Schemes\Models\Course::where('id', $schemeId)
                ->where('instructor_id', $user->id)
                ->exists();
        }

        return false;
    }
}
