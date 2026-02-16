<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserIncludeRequest extends FormRequest
{
    private array $allowedIncludes = [
        'privacy_settings',
        'gamification_stats',
        'enrollments',
        'managed_courses',
        'received_overrides',
        'granted_overrides',
        'badges',
        'challenges',
        'points',
        'levels',
        'milestones',
        'learning_streaks',
        'submissions',
        'assignments',
        'grades',
        'posts',
        'threads',
        'activities',
        'media',
        'roles',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'include' => 'nullable|string',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        if (isset($data['include'])) {
            $requestedIncludes = explode(',', $data['include']);
            $requestedIncludes = array_map('trim', $requestedIncludes);
            $requestedIncludes = array_filter($requestedIncludes);

            foreach ($requestedIncludes as $include) {
                if (! in_array($include, $this->allowedIncludes, true)) {
                    throw ValidationException::withMessages([
                        'include' => "Include '{$include}' is not allowed. Allowed includes: ".implode(', ', $this->allowedIncludes),
                    ]);
                }
            }

            $data['include'] = $requestedIncludes;
        } else {
            $data['include'] = [];
        }

        return $data;
    }

    public function getIncludes(): array
    {
        return $this->validated()['include'];
    }
}
