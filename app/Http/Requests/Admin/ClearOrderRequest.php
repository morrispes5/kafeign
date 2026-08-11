<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ClearOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by the `auth` middleware on the route, same as every other admin FormRequest here
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'cash_received' => [
                'nullable',
                'integer',
                'min:0',
                'required_if:payment_method,'.PaymentMethod::Cash->value,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.enum' => 'Metode pembayaran tidak dikenal.',
            'cash_received.required_if' => 'Uang diterima wajib diisi untuk pembayaran tunai.',
            'cash_received.integer' => 'Uang diterima harus berupa angka.',
            'cash_received.min' => 'Uang diterima tidak boleh negatif.',
        ];
    }

    /**
     * cash_received >= total can't be a static rule in rules() above:
     * "total" isn't a field on this request, it has to be read from the
     * order's current order_items. This is a fast, well-formatted PRE-
     * check so the cashier sees a clear inline error immediately — the
     * actual correctness guarantee is the identical check re-run inside
     * Admin\OrderController::clear()'s settlement transaction, which
     * catches the narrow case where the total changes in the gap between
     * this check and that one (e.g. a customer's cart submit landing at
     * that exact moment).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('payment_method')) {
                return; // let the cashier fix the method first
            }

            $paymentMethod = PaymentMethod::tryFrom((string) $this->input('payment_method'));
            if (! $paymentMethod?->requiresCashReceived() || $validator->errors()->has('cash_received')) {
                return;
            }

            /** @var Order $order */
            $order = $this->route('order');
            $liveTotal = (int) $order->orderItems()->sum('subtotal');
            $cashReceived = (int) $this->input('cash_received');

            if ($cashReceived < $liveTotal) {
                $validator->errors()->add(
                    'cash_received',
                    'Uang diterima (Rp'.number_format($cashReceived, 0, ',', '.').') kurang dari total Rp'
                        .number_format($liveTotal, 0, ',', '.').'.'
                );
            }
        });
    }
}
