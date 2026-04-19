<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class ActivityLog extends SpatieActivity
{
    use \Modules\Common\Traits\PgSearchable;

    protected array $searchable_columns = [
        'description',
        'event',
        'log_name',
    ];

    protected $appends = ['device_info'];

    
    public function getDeviceInfoAttribute(): string
    {
        $parts = array_filter([$this->browser, $this->browser_version, $this->platform]);

        return $parts ? implode(' / ', $parts) : '-';
    }

    
    public function getDeviceIconAttribute(): string
    {
        return match ($this->device_type) {
            'mobile' => 'smartphone',
            'tablet' => 'tablet',
            'desktop' => 'monitor',
            default => 'help-circle',
        };
    }

    
    
    public function scopeCreatedAtBetween($query, ...$dates)
    {
        
        if (count($dates) === 1 && is_string($dates[0]) && str_contains($dates[0], ',')) {
            $dates = explode(',', $dates[0]);
        }
        
        if (count($dates) === 1 && is_array($dates[0])) {
            $dates = $dates[0];
        }

        if (count($dates) >= 2) {
            return $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($dates[0])->startOfDay(),
                \Carbon\Carbon::parse($dates[1])->endOfDay(),
            ]);
        }

        return $query;
    }
}
