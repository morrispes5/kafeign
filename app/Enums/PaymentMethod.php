<?php

namespace App\Enums;

/**
 * How the customer paid when the cashier cleared the table. Stored on
 * Order only at the moment of payment — an ongoing or cancelled order's
 * `payment_method` column is always NULL, never one of these cases.
 */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qris = 'qris';
    case Card = 'card';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Qris => 'QRIS',
            self::Card => 'Kartu',
            self::Transfer => 'Transfer',
        };
    }

    /**
     * Whether this method needs the cash_received/change_due fields at
     * all. Only cash has physical change to hand back — QRIS/kartu/
     * transfer settle for the exact amount, so those three never touch
     * cash_received.
     */
    public function requiresCashReceived(): bool
    {
        return $this === self::Cash;
    }
}
