<?php

namespace Modules\Common\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auth\Http\Resources\UserResource;

class ActivityLogResource extends JsonResource
{
    public function toArray($request)
    {
        $properties = $this->properties ?? [];

        // Ensure ip_address exists in properties if ip exists
        if (isset($properties['ip']) && !isset($properties['ip_address'])) {
            $properties['ip_address'] = $properties['ip'];
        }

        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'causer_type' => $this->causer_type,
            'causer_id' => $this->causer_id,
            'properties' => $properties,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'event' => $this->event,
            'batch_uuid' => $this->batch_uuid,
            'device_info' => $this->device_info, 
            'location' => [
                'city' => $properties['city'] ?? null,
                'region' => $properties['region'] ?? null,
                'country' => $properties['country'] ?? null,
            ],

            'causer' => $this->whenLoaded('causer', function () {
                if ($this->causer instanceof \Modules\Auth\Models\User) {
                     return $this->formatUser($this->causer);
                }
                return $this->causer;
            }),

            'subject' => $this->whenLoaded('subject', function () {
                if ($this->subject instanceof \Modules\Auth\Models\User) {
                    return $this->formatUser($this->subject);
                }
                return $this->subject;
            }),
        ];
    }

    private function formatUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'avatar_url' => $user->avatar_url,
            'status' => $user->status instanceof \Modules\Auth\Enums\UserStatus ? $user->status->value : $user->status,
            'account_status' => $user->account_status,
        ];
    }
}
