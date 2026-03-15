<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgressResource extends JsonResource
{
    public function toArray($request): array
    {
        // The resource receives an array with 'course' and 'units' keys
        // from ProgressionStateProcessor::getCourseProgressData
        return $this->resource;
    }
}
