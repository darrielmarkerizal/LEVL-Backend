<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class XpAwardResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'xp_awarded' => $this->points,
            'reason' => $this->reason,
            'description' => $this->description,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'xp_source_code' => $this->xp_source_code,
            'old_level' => $this->old_level,
            'new_level' => $this->new_level,
            'leveled_up' => $this->triggered_level_up,
            'total_xp' => $this->when($this->user, fn () => $this->user->gamificationStats->total_xp ?? 0),
            'current_level' => $this->when($this->user, fn () => $this->user->gamificationStats->global_level ?? 1),
            'awarded_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
