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
            'type' => $this->type,
            'level_tag' => $this->level_tag,
            'enrollment_type' => $this->enrollment_type,
            'status' => $this->status,
            'enrollment_status' => $isStudent ? $enrollment?->status?->value : null,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'thumbnail' => $this->whenLoaded('media') ? ($this->getFirstMedia('thumbnail')?->getUrl() ?? '') : '',
            'banner' => $this->whenLoaded('media') ? ($this->getFirstMedia('banner')?->getUrl() ?? '') : '',
            'category' => $this->whenLoaded('category'),
            'tags' => $this->whenLoaded('tags'),
            'instructor' => $this->whenLoaded('instructor', fn () => $this->mapUserSummary($this->instructor)),
            'instructor_count' => $this->when(array_key_exists('admins_count', $this->getAttributes()), $this->admins_count),
            'enrollments_count' => $this->when(array_key_exists('enrollments_count', $this->getAttributes()), $this->enrollments_count),
        ];

        if ($isManager) {
            $data['creator'] = $this->whenLoaded('admins', fn () => $this->mapUserSummary($this->admins->first()));
            $data['instructor_list'] = $this->whenLoaded('admins', fn () => $this->mapUsersSummary($this->admins));
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
            'account_status' => $user->account_status,
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
            return $this->admins()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('Instructor')) {
            return $this->instructor_id === $user->id;
        }

        return false;
    }
}
