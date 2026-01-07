<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'enrollments' => [
                'total_enrolled' => $this->resource['enrollments']['total_enrolled'] ?? 0,
                'total_completed' => $this->resource['enrollments']['total_completed'] ?? 0,
                'in_progress' => $this->resource['enrollments']['in_progress'] ?? 0,
            ],
            'gamification' => [
                'total_points' => $this->resource['gamification']['total_points'] ?? 0,
                'current_level' => $this->resource['gamification']['current_level'] ?? 1,
                'badges_earned' => $this->resource['gamification']['badges_earned'] ?? 0,
                'learning_streak' => $this->resource['gamification']['learning_streak'] ?? 0,
            ],
            'performance' => [
                'completion_rate' => $this->resource['performance']['completion_rate'] ?? 0.0,
                'average_score' => $this->resource['performance']['average_score'] ?? 0.0,
            ],
            'activity' => [
                'activities_last_30_days' => $this->resource['activity']['activities_last_30_days'] ?? 0,
            ],
        ];
    }
}
