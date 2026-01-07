<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id ?? null,
            'name' => $this->resource->badge->name ?? $this->resource->name ?? null,
            'description' => $this->resource->badge->description ?? $this->resource->description ?? null,
            'icon_url' => $this->resource->badge->icon_url ?? $this->resource->icon_url ?? null,
            'earned_at' => $this->resource->earned_at ?? $this->resource->created_at ?? null,
            'order' => $this->when(isset($this->resource->order), $this->resource->order),
        ];
    }
}
