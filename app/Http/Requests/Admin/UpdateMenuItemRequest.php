<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_new' => ['nullable', 'boolean'],
            'is_vdt' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
            // See StoreMenuItemRequest — empty means "tidak dilacak".
            'stock' => ['nullable', 'integer', 'min:0', 'max:100000'],
            // See StoreMenuItemRequest — same reasoning.
            'image' => ['nullable', 'image', 'max:'.config('kafeign.menu_photo.max_kb')],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}
