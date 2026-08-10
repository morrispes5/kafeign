<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnterTableRequest extends FormRequest
{
    /**
     * No login/authorization concept for customers — anyone can enter any
     * table number, that's the whole point of the QR-code flow.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'integer', 'between:1,36', 'exists:tables,number'],
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'Masukkan nomor meja terlebih dahulu.',
            'number.integer' => 'Nomor meja harus berupa angka.',
            'number.between' => 'Nomor meja harus antara 1 sampai 36.',
            'number.exists' => 'Nomor meja tidak ditemukan.',
        ];
    }
}
