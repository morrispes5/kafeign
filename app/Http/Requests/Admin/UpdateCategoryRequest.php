<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
            ],
            'icon' => ['required', Rule::in(array_keys(Category::ICONS))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
