<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_item_id' => [
                'required',
                'integer',
                Rule::exists('menu_items', 'id')->where('is_available', true),
            ],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_item_id.required' => 'Item menu tidak ditemukan.',
            'menu_item_id.exists' => 'Item ini sedang tidak tersedia.',
        ];
    }
}
