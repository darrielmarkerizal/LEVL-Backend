<?php

declare(strict_types=1);

namespace Modules\Common\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Media $this */
        return [
            'id' => $this->uuid ?? $this->id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => $this->getFullUrl(),
            'thumb_url' => $this->hasGeneratedConversion('thumb') ? $this->getFullUrl('thumb') : null,
            'created_at' => $this->created_at,
        ];
    }
}
