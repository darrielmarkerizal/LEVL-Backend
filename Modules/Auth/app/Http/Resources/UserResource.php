<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auth\Enums\UserStatus;



class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'name' => $this['name'] ?? null,
            'email' => $this['email'] ?? null,
            'username' => $this['username'] ?? null,
            'avatar' => $this['avatar_url'] ?? null,
            'status' => isset($this['status']) && $this['status'] instanceof UserStatus 
                ? $this['status']->value 
                : (string) ($this['status'] ?? ''),
            'created_at' => $this->formatDate($this['created_at'] ?? null),
            'email_verified_at' => $this->formatDate($this['email_verified_at'] ?? null),
            'role' => $this->getRole(),
        ];
    }

    protected function formatDate(mixed $date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format(\DateTimeInterface::ATOM);
        }
        return $date ? (string) $date : null;
    }

    protected function getRole(): ?string
    {
        // If roles is passed directly in the array (as Collection or Array)
        if (isset($this['roles'])) {
            $roles = $this['roles'];
            if ($roles instanceof \Illuminate\Support\Collection) {
                return $roles->first();
            }
            if (is_array($roles)) {
                return $roles[0] ?? null;
            }
        }
        
        // If resource is a User model object
        if ($this->resource instanceof \Modules\Auth\Models\User) {
            return $this->resource->getRoleNames()->first();
        }
        
        // Fallback for generic object with roles relation
        if (is_object($this->resource) && isset($this->resource->roles)) {
             return $this->resource->roles->first()?->name;
        }

        return null;
    }
}
