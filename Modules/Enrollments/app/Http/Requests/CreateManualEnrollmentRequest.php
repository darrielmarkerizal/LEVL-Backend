<?php

declare(strict_types=1);

namespace Modules\Enrollments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Enrollments\Enums\EnrollmentStatus;

class CreateManualEnrollmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_slug' => ['required', 'string', 'exists:courses,slug'],
            'enrollment_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'initial_status' => ['required', 'string', 'in:active,pending'],
            'is_notify_student' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function messages(): array
    {
        return [
            'student_id.required' => __('validation.required', ['attribute' => 'student_id']),
            'student_id.exists' => __('validation.exists', ['attribute' => 'student']),
            'course_slug.required' => __('validation.required', ['attribute' => 'course_slug']),
            'course_slug.exists' => __('validation.exists', ['attribute' => 'course']),
            'initial_status.required' => __('validation.required', ['attribute' => 'initial_status']),
            'initial_status.in' => __('validation.in', ['attribute' => 'initial_status']),
        ];
    }
}
