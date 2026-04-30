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
                    'description' => $this->course->short_desc ?? null,
                    'status' => $this->course->status ?? null,
                ];
            }),

            'instructor_list' => $this->whenLoaded('course', function () {
                $instructors = collect([]);

                if ($this->course && $this->course->relationLoaded('instructor') && $this->course->instructor) {
                    $instructors->push([
                        'id' => $this->course->instructor->id,
                        'name' => $this->course->instructor->name,
                        'email' => $this->course->instructor->email,
                    ]);
                }

                if ($this->course && $this->course->relationLoaded('instructors') && $this->course->instructors) {
                    foreach ($this->course->instructors as $inst) {
                        $instructors->push([
                            'id' => $inst->id,
                            'name' => $inst->name,
                            'email' => $inst->email,
                        ]);
                    }
                }

                return $instructors->unique('id')->values()->all();
            }),
        ];
    }
}
