<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type?->value,
            'category' => $this->category,
            'rarity' => $this->rarity?->value,
            'xp_reward' => $this->xp_reward,
            'active' => $this->active,
            'threshold' => $this->threshold,
            'is_repeatable' => $this->is_repeatable,
            'max_awards_per_user' => $this->max_awards_per_user,
            'icon_url' => $this->icon_url,
            'icon_thumb_url' => $this->icon_thumb_url,
            'is_earned' => $this->when(isset($this->is_earned), $this->is_earned ?? false),
            'earned_at' => $this->when(isset($this->earned_at), $this->earned_at?->toISOString()),
            'progress' => $this->when(isset($this->progress), $this->progress),
            'rules' => $this->whenLoaded('rules', function () {
                return $this->rules->map(function ($rule) {
                    return [
                        'id' => $rule->id,
                        'event_trigger' => $rule->event_trigger,
                        'conditions' => $rule->conditions,
                        'priority' => $rule->priority,
                        'cooldown_seconds' => $rule->cooldown_seconds,
                        'rule_enabled' => $rule->rule_enabled,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
