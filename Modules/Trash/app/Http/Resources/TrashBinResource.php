<?php

declare(strict_types=1);

namespace Modules\Trash\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrashBinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'resource_type' => $this->resource_type,
            'resource_label' => $this->resource_label,
            'trashable_type' => $this->trashable_type,
            'trashable_id' => $this->trashable_id,
            'group_uuid' => $this->group_uuid,
            'root_resource_type' => $this->root_resource_type,
            'root_resource_id' => $this->root_resource_id,
            'original_status' => $this->original_status,
            'trashed_status' => $this->trashed_status,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->deleted_at,
            'expires_at' => $this->expires_at,
            'metadata' => $this->metadata,
            'restored_at' => $this->restored_at,
            'force_deleted_at' => $this->force_deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
