<?php

namespace Modules\Content\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Auth\Models\User;
use Modules\Content\Models\News;

class NewsPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, News $news)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->hasRole('Admin') || $user->hasRole('Instructor');
    }

    public function update(User $user, News $news)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $user->id === $news->author_id;
    }

    public function delete(User $user, News $news)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $user->id === $news->author_id;
    }

    public function publish(User $user, News $news)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $user->id === $news->author_id;
    }

    public function schedule(User $user, News $news)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $user->id === $news->author_id;
    }
}
