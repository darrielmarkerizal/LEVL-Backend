<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Models\Course;

class CreateThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $forumableType = $this->input('forumable_type', Course::class);
        $forumableId = (int) $this->input('forumable_id', 0);
        $userId = $this->user()->id;

        return match ($forumableType) {
            Course::class => $this->canAccessCourse($userId, $forumableId),
            \Modules\Learning\Models\Unit::class => $this->canAccessUnit($userId, $forumableId),
            \Modules\Learning\Models\Lesson::class => $this->canAccessLesson($userId, $forumableId),
            \Modules\Learning\Models\Assignment::class => $this->canAccessAssignment($userId, $forumableId),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'forumable_type' => [
                'required',
                'string',
                Rule::in([
                    Course::class,
                    \Modules\Learning\Models\Unit::class,
                    \Modules\Learning\Models\Lesson::class,
                    \Modules\Learning\Models\Assignment::class,
                ]),
            ],
            'forumable_id' => 'required|integer|min:1',
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:1|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('validation.required', ['attribute' => __('validation.attributes.title')]),
            'title.min' => __('validation.min.string', ['attribute' => __('validation.attributes.title'), 'min' => 3]),
            'title.max' => __('validation.max.string', ['attribute' => __('validation.attributes.title'), 'max' => 255]),
            'content.required' => __('validation.required', ['attribute' => __('validation.attributes.content')]),
            'content.min' => __('validation.min.string', ['attribute' => __('validation.attributes.content'), 'min' => 1]),
            'content.max' => __('validation.max.string', ['attribute' => __('validation.attributes.content'), 'max' => 5000]),
            'forumable_type.required' => __('validation.required', ['attribute' => 'forum type']),
            'forumable_type.in' => __('validation.in', ['attribute' => 'forum type']),
            'forumable_id.required' => __('validation.required', ['attribute' => 'forum id']),
            'forumable_id.integer' => __('validation.integer', ['attribute' => 'forum id']),
        ];
    }

    private function canAccessCourse(int $userId, int $courseId): bool
    {
        if ($this->user()->hasRole(['admin', 'instructor'])) {
            $course = Course::find($courseId);
            if ($this->user()->hasRole('instructor') && $course->instructor_id !== $userId) {
                return Enrollment::where('user_id', $userId)->where('course_id', $courseId)->exists();
            }

            return true;
        }

        return Enrollment::where('user_id', $userId)->where('course_id', $courseId)->exists();
    }

    private function canAccessUnit(int $userId, int $unitId): bool
    {
        $unit = \Modules\Learning\Models\Unit::find($unitId);
        if (!$unit) {
            return false;
        }

        return $this->canAccessCourse($userId, $unit->course_id);
    }

    private function canAccessLesson(int $userId, int $lessonId): bool
    {
        $lesson = \Modules\Learning\Models\Lesson::find($lessonId);
        if (!$lesson) {
            return false;
        }

        return $this->canAccessUnit($userId, $lesson->unit_id);
    }

    private function canAccessAssignment(int $userId, int $assignmentId): bool
    {
        $assignment = \Modules\Learning\Models\Assignment::find($assignmentId);
        if (!$assignment) {
            return false;
        }

        return $this->canAccessUnit($userId, $assignment->unit_id);
    }
}
