<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;

final class SeederDate
{
    public static function randomPastCarbon(int $maxDaysAgo = 180): Carbon
    {
        return self::randomPastCarbonBetween(0, $maxDaysAgo);
    }

    public static function randomPastCarbonBetween(int $minDaysAgo, int $maxDaysAgo): Carbon
    {
        $daysAgo = random_int($minDaysAgo, $maxDaysAgo);

        return Carbon::now()
            ->subDays($daysAgo)
            ->subMinutes(random_int(0, 1439));
    }

    public static function randomPastDateTime(int $maxDaysAgo = 180): string
    {
        return self::randomPastCarbon($maxDaysAgo)->toDateTimeString();
    }

    public static function randomPastDateTimeBetween(int $minDaysAgo, int $maxDaysAgo): string
    {
        return self::randomPastCarbonBetween($minDaysAgo, $maxDaysAgo)->toDateTimeString();
    }

    public static function randomPastDate(int $maxDaysAgo = 180): string
    {
        return self::randomPastCarbon($maxDaysAgo)->toDateString();
    }
}