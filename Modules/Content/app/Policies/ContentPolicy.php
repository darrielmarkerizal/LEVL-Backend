<?php

namespace Modules\Content\Policies;

use Modules\Auth\Models\User;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;

class ContentPolicy
{
    
    public function view(User $user, $content): bool
    {
        
        if ($content->isPublished()) {
            return true;
        }

        
        return $user->id === $content->author_id || $user->hasRole('admin');
    }

    
    public function createAnnouncement(User $user): bool
    {
        
        return $user->hasRole('admin');
    }

    
    public function createNews(User $user): bool
    {
        
        return $user->hasRole('admin') || $user->hasRole('instructor');
    }

    
    public function createCourseAnnouncement(User $user, int $courseId): bool
    {
        
        if ($user->hasRole('admin')) {
            return true;
        }

        
        if ($user->hasRole('instructor')) {
            return \Modules\Schemes\Models\Course::where('id', $courseId)
                ->where('instructor_id', $user->id)
                ->exists();
        }

        return false;
    }

    
    public function update(User $user, $content): bool
    {
        
        if ($user->hasRole('admin')) {
            return true;
        }

        
        if ($user->id === $content->author_id) {
            return true;
        }

        
        if ($content instanceof Announcement && $content->course_id) {
            return $user->hasRole('instructor') &&
                \Modules\Schemes\Models\Course::where('id', $content->course_id)
                    ->where('instructor_id', $user->id)
                    ->exists();
        }

        return false;
    }

    
    public function delete(User $user, $content): bool
    {
        
        if ($user->hasRole('admin')) {
            return true;
        }

        
        if ($user->id === $content->author_id) {
            return true;
        }

        
        if ($content instanceof Announcement && $content->course_id) {
            return $user->hasRole('instructor') &&
                \Modules\Schemes\Models\Course::where('id', $content->course_id)
                    ->where('instructor_id', $user->id)
                    ->exists();
        }

        return false;
    }

    
    public function publish(User $user, $content): bool
    {
        
        if ($user->hasRole('admin')) {
            return true;
        }

        
        if ($content instanceof News && $user->id === $content->author_id && $user->hasRole('instructor')) {
            return true;
        }

        
        if ($content instanceof Announcement && $content->course_id) {
            return $user->hasRole('instructor') &&
                \Modules\Schemes\Models\Course::where('id', $content->course_id)
                    ->where('instructor_id', $user->id)
                    ->exists();
        }

        return false;
    }

    
    public function schedule(User $user, $content): bool
    {
        
        return $this->publish($user, $content);
    }

    
    public function viewStatistics(User $user): bool
    {
        
        return $user->hasRole('admin') || $user->hasRole('instructor');
    }
}
