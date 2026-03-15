<?php

declare(strict_types=1);

namespace Modules\Notifications\Policies;

use Modules\Auth\Models\User;
use Modules\Notifications\app\Enums\PostStatus;
use Modules\Notifications\app\Models\Post;

class PostPolicy
{
    /**
     * Determine if the user can create posts.
     * Only Admin role can create posts.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine if the user can update posts.
     * Only Admin role can update posts.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine if the user can delete posts.
     * Only Admin role can delete posts.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine if the user can publish posts.
     * Only Admin role can publish posts.
     */
    public function publish(User $user, Post $post): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine if the user can schedule posts.
     * Only Admin role can schedule posts.
     */
    public function schedule(User $user, Post $post): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine if the user can perform bulk operations.
     * Only Admin role can perform bulk operations.
     */
    public function bulkOperations(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine if the user can manage trash (restore/force delete).
     * Only Admin role can manage trash.
     */
    public function manageTrash(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine if the user can view a post.
     * Published posts are visible to users whose roles match target audiences.
     * Draft and scheduled posts are only visible to admins.
     */
    public function view(User $user, Post $post): bool
    {
        // Admins can view all posts regardless of status
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Only published posts are visible to non-admin users
        if ($post->status !== PostStatus::PUBLISHED) {
            return false;
        }

        // Check if user's role is in the post's target audiences
        $userRole = $this->getUserRole($user);
        
        if (!$userRole) {
            return false;
        }

        return $post->audiences()
            ->where('role', $userRole)
            ->exists();
    }

    /**
     * Determine if the user can mark a post as viewed.
     * Delegates to the view() method.
     */
    public function markAsViewed(User $user, Post $post): bool
    {
        return $this->view($user, $post);
    }

    /**
     * Get the user's role for audience matching.
     * Maps Spatie role names to PostAudienceRole enum values.
     */
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
