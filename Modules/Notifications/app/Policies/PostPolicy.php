<?php

declare(strict_types=1);

namespace Modules\Notifications\Policies;

use Modules\Auth\Models\User;
use Modules\Notifications\Enums\PostStatus;
use Modules\Notifications\Models\Post;

class PostPolicy
{
    
    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    
    public function update(User $user, Post $post): bool
    {
        return $user->hasRole('Admin');
    }

    
    public function delete(User $user, Post $post): bool
    {
        return $user->hasRole('Admin');
    }

    
    public function publish(User $user, Post $post): bool
    {
        return $user->hasRole('Admin');
    }

    
    public function schedule(User $user, Post $post): bool
    {
        return $user->hasRole('Admin');
    }

    
    public function bulkOperations(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    
    public function manageTrash(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    
    public function view(User $user, Post $post): bool
    {
        
        if ($user->hasRole('Admin')) {
            return true;
        }

        
        if ($post->status !== PostStatus::PUBLISHED) {
            return false;
        }

        
        $userRole = $this->getUserRole($user);

        if (! $userRole) {
            return false;
        }

        return $post->audiences()
            ->where('role', $userRole)
            ->exists();
    }

    
    public function markAsViewed(User $user, Post $post): bool
    {
        return $this->view($user, $post);
    }

    
    private function getUserRole(User $user): ?string
    {
        if ($user->hasRole('Student')) {
            return 'student';
        }

        if ($user->hasRole('Instructor')) {
            return 'instructor';
        }

        if ($user->hasRole('Admin')) {
            return 'admin';
        }

        return null;
    }
}
