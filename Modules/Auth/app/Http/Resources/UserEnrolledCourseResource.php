<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserEnrolledCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $statusValue = $this->status?->value ?? (string) $this->status;
        $progressPercent = $statusValue === 'pending'
            ? 0
            : (float) ($this->courseProgress?->progress_percent ?? 0);

        return [
            'enrollment_id' => $this->id,
            'scheme_name' => $this->course?->title,
            'progress_percentage' => round($progressPercent, 2),
            'status' => $this->status,
            'enrolled_at' => $this->enrolled_at?->toISOString(),
        ];
    }
}
