<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;
use Modules\Notifications\Enums\PostAudienceRole;
use Modules\Notifications\Enums\PostCategory;
use Modules\Notifications\Enums\PostStatus;

class StorePostRequest extends FormRequest
{
    use HasApiValidation;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('api')->check() && auth('api')->user()->hasRole('Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string', PostCategory::rule()],
            'status' => ['required', 'string', PostStatus::rule()],
            'audiences' => ['required', 'array', 'min:1'],
            'audiences.*' => ['required', 'string', PostAudienceRole::rule()],
            'notification_channels' => ['nullable', 'array'],
            'notification_channels.*' => ['required', 'string', 'in:email,in_app,push'],
            'is_pinned' => ['nullable', 'boolean'],
            'scheduled_at' => [
                'nullable',
                'required_if:status,scheduled',
                'date',
                'after:now',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => __('validation.required', ['attribute' => __('attributes.title')]),
            'title.max' => __('validation.max.string', ['attribute' => __('attributes.title'), 'max' => 255]),
            'content.required' => __('validation.required', ['attribute' => __('attributes.content')]),
            'category.required' => __('validation.required', ['attribute' => __('attributes.category')]),
            'category.in' => __('validation.in', ['attribute' => __('attributes.category')]),
            'status.required' => __('validation.required', ['attribute' => __('attributes.status')]),
            'status.in' => __('validation.in', ['attribute' => __('attributes.status')]),
            'audiences.required' => __('validation.required', ['attribute' => __('attributes.audiences')]),
            'audiences.array' => __('validation.array', ['attribute' => __('attributes.audiences')]),
            'audiences.min' => __('validation.min.array', ['attribute' => __('attributes.audiences'), 'min' => 1]),
            'audiences.*.in' => __('validation.in', ['attribute' => __('attributes.audience')]),
            'notification_channels.array' => __('validation.array', ['attribute' => __('attributes.notification_channels')]),
            'notification_channels.*.in' => __('validation.in', ['attribute' => __('attributes.notification_channel')]),
            'is_pinned.boolean' => __('validation.boolean', ['attribute' => __('attributes.is_pinned')]),
            'scheduled_at.required_if' => __('validation.required_if', [
                'attribute' => __('attributes.scheduled_at'),
                'other' => __('attributes.status'),
                'value' => 'scheduled',
            ]),
            'scheduled_at.date' => __('validation.date', ['attribute' => __('attributes.scheduled_at')]),
            'scheduled_at.after' => __('validation.after', [
                'attribute' => __('attributes.scheduled_at'),
                'date' => 'now',
            ]),
        ];
    }
}
