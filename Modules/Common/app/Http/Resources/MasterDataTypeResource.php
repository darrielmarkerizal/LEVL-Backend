<?php

declare(strict_types=1);

namespace Modules\Common\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterDataTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['type'] ?? null,
            'type' => $this->resource['type'] ?? null,
            'label' => $this->resource['label'] ?? null,
            'is_crud' => $this->resource['is_crud'] ?? false,
            'count' => $this->resource['count'] ?? 0,
            'last_updated' => $this->resource['last_updated'] ?? null,
        ];
    }
}
