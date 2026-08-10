<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
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
            // 10MB — comfortably above a typical phone photo (a few MB),
            // under the php.ini upload_max_filesize ceiling (12M). The
            // uploaded file gets resized/re-encoded before storage
            // (App\Support\MenuItemImage), so this only bounds what a
            // browser is allowed to send, not what ends up on disk.
            'image' => ['nullable', 'image', 'max:10240'],
        ];
    }
}
