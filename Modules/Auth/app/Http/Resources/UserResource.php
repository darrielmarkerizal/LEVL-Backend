<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auth\Enums\UserStatus;
use Modules\Enrollments\Http\Resources\EnrollmentResource;
use Modules\Forums\Http\Resources\ThreadResource;
use Modules\Learning\Http\Resources\AssignmentIndexResource;
use Modules\Learning\Http\Resources\OverrideResource;
use Modules\Learning\Http\Resources\SubmissionIndexResource;
use Modules\Schemes\Http\Resources\CourseIndexResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isStudent = $this->resource instanceof \Illuminate\Database\Eloquent\Model
            && method_exists($this->resource, 'hasRole')
            && $this->resource->hasRole('Student');

        $data = [
            'id' => $this['id'] ?? (is_object($this->resource) ? $this->id : null),
            'name' => $this['name'] ?? (is_object($this->resource) ? $this->name : null),
            'email' => $this['email'] ?? (is_object($this->resource) ? $this->email : null),
            'username' => $this['username'] ?? (is_object($this->resource) ? $this->username : null),
            'avatar_url' => $this['avatar_url'] ?? (is_object($this->resource) ? $this->resource->avatar_url ?? null : null),
            'status' => isset($this['status']) && $this['status'] instanceof UserStatus
                ? $this['status']->value
                : (string) ($this['status'] ?? (is_object($this->resource) ? $this->status : null)),
            'created_at' => $this->formatDate($this['created_at'] ?? (is_object($this->resource) ? $this->created_at : null)),
            'email_verified_at' => $this->formatDate($this['email_verified_at'] ?? (is_object($this->resource) ? $this->email_verified_at : null)),

            // Always include roles (empty array if not present/loaded)
            'roles' => $this->getRoles(),
        ];

        if ($this->resource instanceof \Illuminate\Database\Eloquent\Model) {
            if (! $isStudent && $this->resource->relationLoaded('specialization') && $this->resource->specialization) {
                $data['specialization'] = [
                    'id' => $this->resource->specialization->id,
                    'name' => $this->resource->specialization->name,
                    'value' => $this->resource->specialization->value,
                ];
            }
        } elseif (is_array($this->resource) && array_key_exists('specialization', $this->resource)) {
            if ($this->resource['specialization'] !== null) {
                $data['specialization'] = $this->resource['specialization'];
            }
        }

        // For endpoints that pass an array into the resource (e.g. login),
        // we cannot use whenLoaded() because relationLoaded() doesn't exist on arrays.
        if ($this->resource instanceof \Illuminate\Database\Eloquent\Model) {
            $data['privacySettings'] = $this->resource->relationLoaded('privacySettings')
                ? new ProfilePrivacyResource($this->resource->privacySettings)
                : null;
            $data['enrollments'] = $this->resource->relationLoaded('enrollments')
                ? EnrollmentResource::collection($this->resource->enrollments)
                : null;
            $data['managedCourses'] = $this->resource->relationLoaded('managedCourses')
                ? CourseIndexResource::collection($this->resource->managedCourses)
                : null;
            if (! $isStudent) {
                $data['gamificationStats'] = $this->resource->relationLoaded('gamificationStats')
                    ? ($this->resource->gamificationStats ? $this->resource->gamificationStats->toArray() : null)
                    : null;
                $data['badges'] = $this->resource->relationLoaded('badges')
                    ? $this->resource->badges->toArray()
                    : null;
            }
            $data['points'] = $this->resource->relationLoaded('points')
                ? $this->resource->points->toArray()
                : null;
            $data['levels'] = $this->resource->relationLoaded('levels')
                ? $this->resource->levels->toArray()
                : null;
            $data['learningStreaks'] = $this->resource->relationLoaded('learningStreaks')
                ? $this->resource->learningStreaks->toArray()
                : null;
            $data['submissions'] = $this->resource->relationLoaded('submissions')
                ? SubmissionIndexResource::collection($this->resource->submissions)
                : null;
            $data['assignments'] = $this->resource->relationLoaded('assignments')
                ? AssignmentIndexResource::collection($this->resource->assignments)
                : null;
            $data['receivedOverrides'] = $this->resource->relationLoaded('receivedOverrides')
                ? OverrideResource::collection($this->resource->receivedOverrides)
                : null;
            $data['grantedOverrides'] = $this->resource->relationLoaded('grantedOverrides')
                ? OverrideResource::collection($this->resource->grantedOverrides)
                : null;
            $data['threads'] = $this->resource->relationLoaded('threads')
                ? ThreadResource::collection($this->resource->threads)
                : null;

            $isInstructor = method_exists($this->resource, 'hasRole') && $this->resource->hasRole('Instructor');

            // Add learning statistics for Student and Instructor
            if ($isStudent || $isInstructor) {
                $lastLoginAt = $this->formatDate($this->resource->getAttribute('last_login_at'));
                $data['last_login_at'] = $lastLoginAt;
                $data['learning_statistics'] = $this->resource->getAttribute('learning_statistics')
                    ?? ($isInstructor
                        ? [
                            'courses_taught' => 0,
                            'total_students' => 0,
                            'assignments_graded' => 0,
                            'quizzes_graded' => 0,
                        ]
                        : [
                            'enrolled' => 0,
                            'completed' => 0,
                            'assignments_graded' => 0,
                            'quizzes_graded' => 0,
                        ]);
            }

            if ($isStudent) {
                $data['rank'] = $this->resource->getAttribute('rank');
                $data['total_xp'] = (int) ($this->resource->getAttribute('total_xp') ?? 0);

                $recentBadges = collect($this->resource->badges ?? [])->take(3)->map(function ($userBadge) {
                    $badge = $userBadge->badge;

                    return [
                        'id' => $userBadge->id,
                        'earned_at' => $this->formatDate($userBadge->earned_at),
                        'badge' => $badge ? [
                            'id' => $badge->id,
                            'code' => $badge->code,
                            'name' => $badge->name,
                            'description' => $badge->description,
                            'type' => is_object($badge->type) && property_exists($badge->type, 'value')
                                ? $badge->type->value
                                : $badge->type,
                            'icon_url' => $badge->icon_url,
                        ] : null,
                    ];
                })->values();

                $data['recent_badges'] = $recentBadges;
            }
        } elseif (is_array($this->resource)) {
            // If the resource is an array, only include keys that exist.
            foreach ([
                'privacySettings',
                'enrollments',
                'managedCourses',
                'gamificationStats',
                'badges',
                'points',
                'levels',
                'learningStreaks',
                'submissions',
                'assignments',
                'receivedOverrides',
                'grantedOverrides',
                'threads',
                'specialization',
            ] as $key) {
                if (array_key_exists($key, $this->resource)) {
                    $data[$key] = $this->resource[$key];
                }
            }
        }

        $preserveNullKeys = ['last_login_at'];

        // Remove null keys (but keep empty arrays/false/0), except preserved keys.
        $filtered = array_filter(
            $data,
            static fn ($v, $key) => $v !== null || in_array($key, $preserveNullKeys, true),
            ARRAY_FILTER_USE_BOTH,
        );

        $orderedKeys = [
            'id',
            'name',
            'email',
            'username',
            'avatar_url',
            'status',
            'created_at',
            'email_verified_at',
            'roles',
            'specialization',
            'last_login_at',
            'learning_statistics',
            'rank',
            'total_xp',
            'recent_badges',
            'privacySettings',
            'enrollments',
            'managedCourses',
            'gamificationStats',
            'badges',
            'points',
            'levels',
            'learningStreaks',
            'submissions',
            'assignments',
            'receivedOverrides',
            'grantedOverrides',
            'threads',
        ];

        $ordered = [];
        foreach ($orderedKeys as $key) {
            if (array_key_exists($key, $filtered)) {
                $ordered[$key] = $filtered[$key];
            }
        }

        foreach ($filtered as $key => $value) {
            if (! array_key_exists($key, $ordered)) {
                $ordered[$key] = $value;
            }
        }

        return array_filter(
            $ordered,
            static fn ($v, $key) => $v !== null || in_array($key, $preserveNullKeys, true),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    protected function formatDate(mixed $date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format(\DateTimeInterface::ATOM);
        }

        return $date ? (string) $date : null;
    }

    protected function getRoles(): array
    {
        if (isset($this['roles'])) {
            $roles = $this['roles'];
            if ($roles instanceof \Illuminate\Support\Collection) {
                return $roles->toArray();
            }
            if (is_array($roles)) {
                return $roles;
            }
        }

        // If resource is a User model object
        if ($this->resource instanceof \Modules\Auth\Models\User) {
            return $this->resource->roles->toArray();
        }

        // Fallback for generic object with roles relation
        if (is_object($this->resource) && isset($this->resource->roles)) {
            return $this->resource->roles->toArray();
        }

        return [];
    }
}
