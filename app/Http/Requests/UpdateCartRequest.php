<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    /**
     * Customers are anonymous by design; access to a table is enforced by
     * the EnsureTableSession middleware on the route, not here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Shape only.
     *
     * Whether an item may actually be ordered right now — availability
     * today, stock from Phase 8 — is checked in the controller against
     * MenuItem::available(), so there is exactly one place expressing that
     * rule and the customer gets a message naming the specific item rather
     * than a generic "tidak ditemukan".
     */
    public function rules(): array
    {
        return [
            'menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'quantity' => ['nullable', 'integer', 'min:0', 'max:'.Order::MAX_QUANTITY_PER_ITEM],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_item_id.required' => 'Item menu tidak ditemukan.',
            'menu_item_id.exists' => 'Item menu tidak ditemukan.',
            'quantity.max' => 'Jumlah melebihi batas pemesanan per item.',
        ];
    }
}
