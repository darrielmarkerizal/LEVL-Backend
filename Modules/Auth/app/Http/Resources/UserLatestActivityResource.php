<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLatestActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $actionType = $this->event
            ?? data_get($this->properties, 'action')
            ?? '-';

        return [
            'timestamp' => $this->created_at?->toIso8601String(),
            'action_type' => (string) $actionType,
            'description' => $this->description ?? '-',
        ];
    }
}
