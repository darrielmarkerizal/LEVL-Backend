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
        $forumableSlug = (string) $this->input('forumable_slug', '');
        $forumableId = $this->resolveForumableId($forumableType, $forumableSlug);
        $userId = $this->user()->id;

        if (! $forumableId) {
            return false;
        }

        return match ($forumableType) {
            Course::class => $this->canAccessCourse($userId, $forumableId),
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
                ]),
            ],
            'forumable_slug' => 'required|string',
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:1|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,mp4,webm,ogg,mov,avi|max:51200',
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
            'forumable_slug.required' => __('validation.required', ['attribute' => 'forum slug']),
            'attachments.array' => __('validation.array', ['attribute' => 'attachments']),
            'attachments.max' => __('validation.max.array', ['attribute' => 'attachments', 'max' => 5]),
            'attachments.*.file' => __('validation.file', ['attribute' => 'attachment']),
            'attachments.*.mimes' => __('validation.mimes', ['attribute' => 'attachment', 'values' => 'jpeg,png,jpg,gif,pdf,mp4,webm,ogg,mov,avi']),
            'attachments.*.max' => __('validation.max.file', ['attribute' => 'attachment', 'max' => '50MB']),
        ];
    }

    private function resolveForumableId(string $forumableType, string $forumableSlug): ?int
    {
        return match ($forumableType) {
            Course::class => Course::where('slug', $forumableSlug)->value('id'),
            default => null,
        };
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


}
