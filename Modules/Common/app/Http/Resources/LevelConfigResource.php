<?php

declare(strict_types=1);

namespace Modules\Common\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LevelConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'name' => $this->name,
            'xp_required' => $this->xp_required,
            'rewards' => $this->buildRewards(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    
    private function buildRewards(): array
    {
        $rewards = [];

        
        if ($this->bonus_xp > 0) {
            $rewards['bonus_xp'] = $this->bonus_xp;
        }

        
        $milestoneBadge = $this->whenLoaded('milestoneBadge', function () {
            return $this->milestoneBadge ? [
                'id' => $this->milestoneBadge->id,
                'name' => $this->milestoneBadge->name,
                'slug' => $this->milestoneBadge->slug,
                'description' => $this->milestoneBadge->description,
                'icon_url' => $this->milestoneBadge->icon_url,
                'rarity' => $this->milestoneBadge->rarity?->value,
            ] : null;
        });

        if ($milestoneBadge) {
            $rewards['milestone_badge'] = $milestoneBadge;
        }

        
        if (! empty($this->rewards) && is_array($this->rewards)) {
            $rewards = array_merge($rewards, $this->rewards);
        }

        return $rewards;
    }
}
