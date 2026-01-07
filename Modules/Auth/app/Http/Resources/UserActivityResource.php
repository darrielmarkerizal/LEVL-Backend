<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auth\Models\UserActivity;

class UserActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var UserActivity $activity */
        $activity = $this->resource;

        return [
            'id' => $activity->id,
            'activity_type' => $activity->activity_type,
            'activity_data' => $activity->activity_data,
            'related_type' => $activity->related_type,
            'related_id' => $activity->related_id,
            'created_at' => $activity->created_at?->toISOString(),
        ];
    }
}
