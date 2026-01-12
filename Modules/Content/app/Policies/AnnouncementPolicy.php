<?php

namespace Modules\Content\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Auth\Models\User;
use Modules\Content\Models\Announcement;

class AnnouncementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Announcement $announcement)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->hasRole('Admin') || $user->hasRole('Instructor');
    }

    public function update(User $user, Announcement $announcement)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $user->id === $announcement->author_id;
    }

    public function delete(User $user, Announcement $announcement)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $user->id === $announcement->author_id;
    }

    public function publish(User $user, Announcement $announcement)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $user->id === $announcement->author_id;
    }

    public function schedule(User $user, Announcement $announcement)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasRole('Instructor') && $user->id === $announcement->author_id;
    }
}
