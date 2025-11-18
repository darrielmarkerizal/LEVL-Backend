<?php

namespace Modules\Assessments\Http\Requests\Concerns;

trait HasCommonValidationMessages
{
    public function messagesExerciseStore(): array
    {
        return [
            'scope_type.required' => 'Tipe scope wajib diisi.',
            'scope_type.in' => 'Tipe scope harus berupa course atau program.',
            'scope_id.required' => 'ID scope wajib diisi.',
            'scope_id.integer' => 'ID scope harus berupa angka.',
            'title.required' => 'Judul latihan wajib diisi.',
            'title.string' => 'Judul latihan harus berupa teks.',
            'title.max' => 'Judul latihan maksimal 255 karakter.',
            'type.required' => 'Tipe latihan wajib diisi.',
            'type.in' => 'Tipe latihan harus berupa quiz, exam, assignment, atau homework.',
            'time_limit_minutes.integer' => 'Batas waktu harus berupa angka.',
            'time_limit_minutes.min' => 'Batas waktu minimal 1 menit.',
            'max_score.required' => 'Skor maksimal wajib diisi.',
            'max_score.numeric' => 'Skor maksimal harus berupa angka.',
            'max_score.min' => 'Skor maksimal minimal 0.',
        ];
    }

    public function messagesQuestionStore(): array
    {
        return [
            'question_text.required' => 'Teks pertanyaan wajib diisi.',
            'type.required' => 'Tipe pertanyaan wajib diisi.',
            'type.in' => 'Tipe pertanyaan harus berupa multiple_choice, free_text, file_upload, atau true_false.',
            'score_weight.required' => 'Bobot skor wajib diisi.',
            'score_weight.numeric' => 'Bobot skor harus berupa angka.',
        ];
    }

    public function messagesOptionStore(): array
    {
        return [
            'option_text.required' => 'Teks pilihan wajib diisi.',
            'is_correct.required' => 'Status pilihan benar/salah wajib diisi.',
            'is_correct.boolean' => 'Status pilihan harus berupa boolean.',
        ];
    }

    public function messagesAttemptAnswer(): array
    {
        return [
            'question_id.required' => 'ID pertanyaan wajib diisi.',
            'question_id.exists' => 'Pertanyaan tidak ditemukan.',
            'selected_option_id.exists' => 'Pilihan jawaban tidak ditemukan.',
        ];
    }

    public function messagesFeedback(): array
    {
        return [
            'score_awarded.required' => 'Skor yang diberikan wajib diisi.',
            'score_awarded.numeric' => 'Skor yang diberikan harus berupa angka.',
        ];
    }
}
