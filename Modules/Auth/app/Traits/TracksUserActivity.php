<?php

declare(strict_types=1);

namespace Modules\Auth\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait TracksUserActivity
{
    public function logActivity(string $type, array $data = [], ?Model $related = null)
    {
        $activity = activity('user_activity')
            ->causedBy($this)
            ->withProperties($data)
            ->event($type)
            ->tap(function (\Illuminate\Database\Eloquent\Model $activity) {
                if (! $activity instanceof ActivityLog) {
                    return;
                }

                $deviceInfo = \App\Support\BrowserLogger::getDeviceInfo();
                $activity->ip_address = $deviceInfo['ip_address'] ?? request()->ip();
                $activity->browser = $deviceInfo['browser'] ?? null;
                $activity->browser_version = $deviceInfo['browser_version'] ?? null;
                $activity->platform = $deviceInfo['platform'] ?? null;
                $activity->device = $deviceInfo['device'] ?? null;
                $activity->device_type = $deviceInfo['device_type'] ?? null;
                $activity->city = $deviceInfo['city'] ?? null;
                $activity->region = $deviceInfo['region'] ?? null;
                $activity->country = $deviceInfo['country'] ?? null;
            });

        if ($related) {
            $activity->performedOn($related);
        }

        return $activity->log($type);
    }

    public function getRecentActivities(int $limit = 10)
    {
        return $this->actions()->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    public function actions()
    {
        return $this->morphMany(ActivityLog::class, 'causer');
    }

    public function latestActivity()
    {
        return $this->morphOne(ActivityLog::class, 'causer')->latestOfMany();
    }

    public function getLastActiveRelativeAttribute(): ?string
    {
        if (! $this->relationLoaded('latestActivity')) {
            return null;
        }

        $lastActivity = $this->latestActivity;

        if (! $lastActivity) {
            return null;
        }

        return $lastActivity->created_at->locale('id')->diffForHumans();
    }
}
