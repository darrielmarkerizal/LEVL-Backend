<?php

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'course_id' => 'nullable|exists:courses,id',
            'target_type' => 'required|in:all,role,course',
            'target_value' => 'nullable|string|max:255',
            'priority' => 'nullable|in:low,normal,high',
            'status' => 'nullable|in:draft,published,scheduled',
            'scheduled_at' => 'nullable|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul pengumuman wajib diisi.',
            'content.required' => 'Konten pengumuman wajib diisi.',
            'target_type.required' => 'Tipe target audience wajib dipilih.',
            'target_type.in' => 'Tipe target audience tidak valid.',
            'scheduled_at.after' => 'Waktu publikasi harus di masa depan.',
        ];
    }
}
