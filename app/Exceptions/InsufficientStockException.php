<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a cart cannot be fulfilled at submit time.
 *
 * Carries a per-item map so the customer sees exactly which row is the
 * problem, not just that something went wrong — the cart page paints the
 * offending line red using these keys.
 */
class InsufficientStockException extends RuntimeException
{
    /** @param array<string, string> $itemErrors keyed "cart.{menu_item_id}" */
    public function __construct(
        public readonly array $itemErrors,
    ) {
        parent::__construct('Stok tidak mencukupi untuk sebagian item di keranjang.');
    }
}
