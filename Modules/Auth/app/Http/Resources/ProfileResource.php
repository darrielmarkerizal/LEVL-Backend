<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'] ?? null,
            'name' => $this->resource['name'] ?? null,
            'username' => $this->resource['username'] ?? null,
            'email' => $this->resource['email'] ?? null,
            'phone' => $this->resource['phone'] ?? null,
            'bio' => $this->resource['bio'] ?? null,
            'avatar_url' => $this->resource['avatar_url'] ?? null,
            'account_status' => $this->resource['account_status'] ?? null,
            'last_profile_update' => $this->resource['last_profile_update'] ?? null,
            'created_at' => $this->resource['created_at'] ?? null,
            
            // Conditionally include statistics if present
            'statistics' => $this->when(
                isset($this->resource['statistics']),
                $this->resource['statistics']
            ),
            
            // Conditionally include achievements if present
            'achievements' => $this->when(
                isset($this->resource['achievements']),
                $this->resource['achievements']
            ),
            
            // Conditionally include recent activities if present
            'recent_activities' => $this->when(
                isset($this->resource['recent_activities']),
                $this->resource['recent_activities']
            ),
        ];
    }
}
