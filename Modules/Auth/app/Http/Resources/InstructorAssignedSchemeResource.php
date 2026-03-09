<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorAssignedSchemeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $studentsCount = $this->enrollments?->count() ?? 0;

    $statusValue = $this->status?->value ?? (string) $this->status;
    $statusLabel = $this->status ? $this->status->label() : ucfirst($statusValue);

        return [
            'scheme_id' => $this->id,
            'scheme_name' => $this->title,
            'scheme_code' => $this->code,
            'students_count' => $studentsCount,
            'assigned_at' => $this->created_at?->toIso8601String(),
            'status' => $statusLabel,
        ];
    }
}
