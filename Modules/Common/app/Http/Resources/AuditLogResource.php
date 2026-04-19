<?php

declare(strict_types=1);

namespace Modules\Common\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Common\Models\AuditLog;


class AuditLogResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        
        $auditLog = $this->resource;

        return [
            'id' => $auditLog->id,
            'action' => $auditLog->action,
            'actor' => [
                'id' => $auditLog->actor_id,
                'type' => $auditLog->actor_type,
                'name' => $this->whenLoaded('actor', fn () => $auditLog->actor->name ?? null),
                'username' => $this->whenLoaded('actor', fn () => $auditLog->actor->username ?? null),
                'email' => $this->whenLoaded('actor', fn () => $auditLog->actor->email ?? null),
                'avatar_url' => $this->whenLoaded('actor', fn () => $auditLog->actor->avatar_url ?? null),
            ],
            'subject' => [
                'id' => $auditLog->subject_id,
                'type' => $auditLog->subject_type,
            ],
            'context' => $auditLog->context,
            'created_at' => $auditLog->created_at->toIso8601String(),

            
            'event' => $this->when($auditLog->event !== null, $auditLog->event),
            'target' => $this->when($auditLog->target_type !== null, [
                'id' => $auditLog->target_id,
                'type' => $auditLog->target_type,
            ]),
            'properties' => $this->when($auditLog->properties !== null, $auditLog->properties),
            'logged_at' => $this->when(
                $auditLog->logged_at !== null,
                $auditLog->logged_at?->toIso8601String()
            ),
        ];
    }
}
