<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auth\Models\ProfilePrivacySetting;

class ProfilePrivacyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ProfilePrivacySetting $settings */
        $settings = $this->resource;

        return [
            'profile_visibility' => $settings->profile_visibility,
            'show_email' => $settings->show_email,
            'show_phone' => $settings->show_phone,
            'show_activity_history' => $settings->show_activity_history,
            'show_achievements' => $settings->show_achievements,
            'show_statistics' => $settings->show_statistics,
            'updated_at' => $settings->updated_at?->toISOString(),
        ];
    }
}
