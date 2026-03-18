<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $baseRules = [
            'type' => ['required', 'string', 'in:lesson,assignment,quiz'],
            'title' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:1'],
        ];

        if ($type === 'assignment') {
            $baseRules['submission_type'] = ['required', 'in:file,mixed'];
        }

        return $baseRules;
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipe konten harus diisi',
            'type.in' => 'Tipe konten harus lesson, assignment, atau quiz',
            'title.required' => 'Judul harus diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'order.integer' => 'Order harus berupa angka',
            'order.min' => 'Order minimal 1',
            'submission_type.required' => 'Tipe submission harus diisi untuk assignment',
            'submission_type.in' => 'Tipe submission harus file atau mixed',
        ];
    }
}
