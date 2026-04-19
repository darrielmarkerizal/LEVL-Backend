<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;
use Modules\Notifications\Enums\PostAudienceRole;
use Modules\Notifications\Enums\PostCategory;
use Modules\Notifications\Enums\PostStatus;

class UpdatePostRequest extends FormRequest
{
    use HasApiValidation;

    
    public function authorize(): bool
    {
        return auth('api')->check() && auth('api')->user()->hasRole('Admin');
    }

    
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'category' => ['nullable', 'string', PostCategory::rule()],
            'status' => ['nullable', 'string', PostStatus::rule()],
            'audiences' => ['nullable', 'array', 'min:1'],
            'audiences.*' => ['required', 'string', PostAudienceRole::rule()],
            'notification_channels' => ['nullable', 'array'],
            'notification_channels.*' => ['required', 'string', 'in:email,in_app,push'],
            'is_pinned' => ['nullable', 'boolean'],
            'scheduled_at' => [
                'nullable',
                'date',
                'after:now',
            ],
            'resend_notification_channels' => ['nullable', 'array'],
            'resend_notification_channels.*' => ['required', 'string', 'in:email,in_app,push'],
        ];
    }

    
    public function messages(): array
    {
        return [
            'title.max' => __('validation.max.string', ['attribute' => __('attributes.title'), 'max' => 255]),
            'category.in' => __('validation.in', ['attribute' => __('attributes.category')]),
            'status.in' => __('validation.in', ['attribute' => __('attributes.status')]),
            'audiences.array' => __('validation.array', ['attribute' => __('attributes.audiences')]),
            'audiences.min' => __('validation.min.array', ['attribute' => __('attributes.audiences'), 'min' => 1]),
            'audiences.*.in' => __('validation.in', ['attribute' => __('attributes.audience')]),
            'notification_channels.array' => __('validation.array', ['attribute' => __('attributes.notification_channels')]),
            'notification_channels.*.in' => __('validation.in', ['attribute' => __('attributes.notification_channel')]),
            'is_pinned.boolean' => __('validation.boolean', ['attribute' => __('attributes.is_pinned')]),
            'scheduled_at.date' => __('validation.date', ['attribute' => __('attributes.scheduled_at')]),
            'scheduled_at.after' => __('validation.after', [
                'attribute' => __('attributes.scheduled_at'),
                'date' => 'now',
            ]),
            'resend_notification_channels.array' => __('validation.array', ['attribute' => __('attributes.resend_notification_channels')]),
            'resend_notification_channels.*.in' => __('validation.in', ['attribute' => __('attributes.resend_notification_channel')]),
        ];
    }
}
