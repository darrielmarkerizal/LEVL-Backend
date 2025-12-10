<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardResource extends JsonResource
{
    protected int $rank;

    public function withRank(int $rank): self
    {
        $this->rank = $rank;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'rank' => $this->rank ?? 0,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user?->name ?? 'Unknown',
                'avatar_url' => $this->user?->avatar_url ?? null,
            ],
            'total_xp' => $this->total_xp,
            'level' => $this->global_level,
        ];
    }
}
