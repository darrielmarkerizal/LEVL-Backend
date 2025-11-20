<?php

namespace App\Traits;

trait ValidatesWithCustomMessages
{
    /**
     * Validate request with custom error messages in Indonesian
     */
    protected function validateWithMessages($data, $rules, $customMessages = [])
    {
        $messages = array_merge([
            'required' => 'Kolom :attribute wajib diisi.',
            'email' => 'Format :attribute tidak valid.',
            'unique' => ':attribute sudah terdaftar di sistem.',
            'min' => ':attribute minimal harus :min karakter.',
            'max' => ':attribute maksimal :max karakter.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa angka.',
            'numeric' => ':attribute harus berupa angka.',
            'array' => ':attribute harus berupa array.',
            'date' => ':attribute bukan tanggal yang valid.',
            'in' => ':attribute yang dipilih tidak valid.',
            'regex' => 'Format :attribute tidak valid.',
            'exists' => ':attribute yang dipilih tidak ada.',
            'after' => ':attribute harus tanggal setelah :date.',
            'before' => ':attribute harus tanggal sebelum :date.',
            'json' => ':attribute harus berupa JSON yang valid.',
            'ip' => ':attribute harus berupa IP address yang valid.',
            'url' => ':attribute harus berupa URL yang valid.',
            'active_url' => ':attribute harus berupa URL yang aktif.',
            'distinct' => ':attribute memiliki duplikat nilai.',
            'mimes' => ':attribute harus berupa file dengan tipe: :values.',
            'file' => ':attribute harus berupa file.',
            'image' => ':attribute harus berupa gambar.',
            'sometimes' => ':attribute kadang-kadang diperlukan.',
            'required_if' => ':attribute wajib diisi jika :other adalah :value.',
            'required_unless' => ':attribute wajib diisi kecuali :other adalah :values.',
            'required_with' => ':attribute wajib diisi ketika :values ada.',
            'required_with_all' => ':attribute wajib diisi ketika :values ada.',
            'required_without' => ':attribute wajib diisi ketika :values tidak ada.',
            'required_without_all' => ':attribute wajib diisi ketika :values tidak ada.',
            'same' => ':attribute dan :other harus cocok.',
            'size' => ':attribute harus berukuran :size.',
            'between' => ':attribute harus antara :min dan :max.',
            'gt' => ':attribute harus lebih besar dari :value.',
            'gte' => ':attribute harus lebih besar atau sama dengan :value.',
            'lt' => ':attribute harus lebih kecil dari :value.',
            'lte' => ':attribute harus lebih kecil atau sama dengan :value.',
        ], $customMessages);

        return validator($data, $rules, $messages)->validate();
    }
}
