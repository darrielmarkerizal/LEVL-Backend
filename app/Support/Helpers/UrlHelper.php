<?php

namespace App\Support\Helpers;

use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;

class UrlHelper
{
    
    public static function getFrontendUrl(): string
    {
        return rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
    }

    
    public static function getCourseUrl(Course $course): string
    {
        return self::getFrontendUrl().'/courses/'.$course->slug;
    }

    
    public static function getEnrollmentsUrl(Course $course): string
    {
        return self::getFrontendUrl().'/courses/'.$course->slug.'/enrollments';
    }

    
    public static function getLessonUrl(Course $course, Lesson $lesson): string
    {
        return self::getFrontendUrl().'/courses/'.$course->slug.'/lessons/'.$lesson->slug;
    }

    
    public static function getUnitUrl(Course $course, $unit): string
    {
        return self::getFrontendUrl().'/courses/'.$course->slug.'/units/'.$unit->slug;
    }

    
    public static function getProfileUrl(int $userId): string
    {
        return self::getFrontendUrl().'/profile/'.$userId;
    }

    
    public static function getDashboardUrl(): string
    {
        return self::getFrontendUrl().'/dashboard';
    }
}
