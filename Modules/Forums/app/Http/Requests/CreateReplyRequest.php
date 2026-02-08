<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Enrollments\Models\Enrollment;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;
use Modules\Schemes\Models\Course;

class CreateReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $threadId = (int) $this->route('thread');
        $thread = Thread::find($threadId);

        if (!$thread) {
            return false;
        }

        return match ($thread->forumable_type) {
            Course::class => $this->canAccessCourse($this->user()->id, $thread->forumable_id),
            \Modules\Learning\Models\Unit::class => $this->canAccessUnit($this->user()->id, $thread->forumable_id),
            \Modules\Learning\Models\Lesson::class => $this->canAccessLesson($this->user()->id, $thread->forumable_id),
            \Modules\Learning\Models\Assignment::class => $this->canAccessAssignment($this->user()->id, $thread->forumable_id),
            default => false,
        };
    }

    public function rules(): array
    {
        $threadId = (int) $this->route('thread');

        return [
            'content' => 'required|string|min:1|max:5000',
            'parent_id' => [
                'nullable',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($threadId) {
                    if ($value) {
                        $parent = Reply::find($value);
                        if (!$parent || $parent->thread_id !== $threadId) {
                            $fail('The selected parent reply does not exist in this thread.');
                        } elseif (!$parent->canHaveChildren()) {
                            $fail('This reply has reached the maximum nesting level.');
                        }
                    }
                },
            ],
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,mp4,webm,ogg,mov,avi|max:51200',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => __('validation.required', ['attribute' => __('validation.attributes.content')]),
            'content.min' => __('validation.min.string', ['attribute' => __('validation.attributes.content'), 'min' => 1]),
            'content.max' => __('validation.max.string', ['attribute' => __('validation.attributes.content'), 'max' => 5000]),
            'parent_id.integer' => __('validation.integer', ['attribute' => __('validation.attributes.parent_reply')]),
            'parent_id.min' => __('validation.min.numeric', ['attribute' => __('validation.attributes.parent_reply'), 'min' => 1]),
            'attachments.array' => __('validation.array', ['attribute' => 'attachments']),
            'attachments.max' => __('validation.max.array', ['attribute' => 'attachments', 'max' => 5]),
            'attachments.*.file' => __('validation.file', ['attribute' => 'attachment']),
            'attachments.*.mimes' => __('validation.mimes', ['attribute' => 'attachment', 'values' => 'jpeg,png,jpg,gif,pdf,mp4,webm,ogg,mov,avi']),
            'attachments.*.max' => __('validation.max.file', ['attribute' => 'attachment', 'max' => '50MB']),
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
