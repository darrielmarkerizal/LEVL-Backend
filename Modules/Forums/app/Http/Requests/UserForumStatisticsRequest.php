<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserForumStatisticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter.period_start' => 'nullable|date',
            'filter.period_end' => 'nullable|date|after_or_equal:filter.period_start',
        ];
    }
}
