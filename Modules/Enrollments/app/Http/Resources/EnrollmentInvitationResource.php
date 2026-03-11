<?php

declare(strict_types=1);

namespace Modules\Enrollments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'enrolled_at' => $this->enrolled_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),

            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course->id,
                    'title' => $this->course->title,
                    'slug' => $this->course->slug,
                    'code' => $this->course->code ?? null,
                    'description' => $this->course->description ?? null,
                    'status' => $this->course->status ?? null,
                ];
            }),

            'instructor' => $this->whenLoaded('course', function () {
                if ($this->course && $this->course->relationLoaded('instructor') && $this->course->instructor) {
                    return [
                        'id' => $this->course->instructor->id,
                        'name' => $this->course->instructor->name,
                        'email' => $this->course->instructor->email,
                    ];
                }

                return null;
            }),
        ];
    }
}
