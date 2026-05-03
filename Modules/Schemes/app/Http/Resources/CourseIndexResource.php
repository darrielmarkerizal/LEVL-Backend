<?php

namespace Modules\Schemes\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = auth('api')->user();
        $enrollment = null;
        $isManager = $this->isManager($user);
        $isStudent = $user && $user->hasRole('Student');

        if ($isStudent) {
            $enrollment = $this->enrollments->where('user_id', $user->id)->first();
        }

        $data = [
            'id' => $this->id,
            'code' => $this->code,
            'slug' => $this->slug,
            'title' => $this->title,
            'short_desc' => $this->short_desc,
            'type' => $this->type?->value,
            'type_label' => $this->type ? __('enums.course_type.'.$this->type->value) : null,
            'level_tag' => $this->level_tag?->value,
            'level_tag_label' => $this->level_tag ? __('enums.level_tag.'.$this->level_tag->value) : null,
            'enrollment_type' => $this->enrollment_type?->value,
            'enrollment_type_label' => $this->enrollment_type ? __('enums.enrollment_type.'.$this->enrollment_type->value) : null,
            'status' => $this->status?->value,
            'status_label' => $this->status ? __('enums.course_status.'.$this->status->value) : null,
            'enrollment_status' => $isStudent ? $enrollment?->status?->value : null,
            'enrollment_status_label' => $isStudent && $enrollment?->status ? __('enums.enrollment_status.'.$enrollment->status->value) : null,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'thumbnail' => $this->whenLoaded('media') ? ($this->getFirstMedia('thumbnail')?->getUrl() ?? '') : '',
            'banner' => $this->whenLoaded('media') ? ($this->getFirstMedia('banner')?->getUrl() ?? '') : '',
            'category' => $this->whenLoaded('category'),
            'tags' => $this->whenLoaded('tags'),
            'instructor' => $this->whenLoaded('instructor', fn () => $this->mapUserSummary($this->instructor)),
            'instructor_list' => $this->whenLoaded('instructors', fn () => $this->mapUsersSummary($this->instructors)),
            'instructor_count' => $this->when(array_key_exists('instructors_count', $this->getAttributes()), $this->instructors_count),
            'enrollments_count' => $this->when(array_key_exists('enrollments_count', $this->getAttributes()), $this->enrollments_count),
        ];

        if ($isStudent && $enrollment) {
            $data['progress'] = $this->getProgressInfo($enrollment);
        }

        if ($isManager) {
            $data['creator'] = $this->whenLoaded('instructors', fn () => $this->mapUserSummary($this->instructors->first()));
            $data['enrollments'] = $this->when(request()->has('include') && str_contains(request('include'), 'enrollments'), $this->whenLoaded('enrollments'));
        }

        if ($isManager || ($isStudent && $enrollment && $enrollment->status->value === 'active')) {
            $data['units'] = $this->whenLoaded('units');
            $data['lessons'] = $this->whenLoaded('lessons');
            $data['quizzes'] = $this->whenLoaded('quizzes');
            $data['assignments'] = $this->whenLoaded('assignments');
        }

        return $data;
    }

    private function mapUserSummary($user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar_url' => $user->avatar_url,
            'status' => $user->status,
        ];
    }

    private function mapUsersSummary(iterable $users): array
    {
        $result = [];
        foreach ($users as $user) {
            $result[] = $this->mapUserSummary($user);
        }

        return $result;
    }

    private function isManager(?object $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->hasRole('Instructor')) {
            return $this->instructors()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    private function getProgressInfo($enrollment): array
    {
        $prerequisiteService = app(\Modules\Schemes\Services\PrerequisiteService::class);

        return $prerequisiteService->getCourseProgressInfo(
            $this->id,
            $enrollment->id,
            $enrollment->user_id
        );
    }
}
