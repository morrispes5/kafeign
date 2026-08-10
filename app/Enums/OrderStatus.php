<?php

namespace App\Enums;

/**
 * The lifecycle of a table's tab (an Order row).
 *
 * Ongoing:   the tab is open; customers can keep adding order_items to it.
 * Paid:      the cashier cleared the table after the customer paid in
 *            person — the tab is closed and stays in the database as
 *            history, but is no longer "active".
 * Cancelled: an ongoing tab was closed without payment (e.g. the customer
 *            left without ordering) — an escape hatch for the admin side,
 *            not something the customer flow triggers itself.
 */
enum OrderStatus: string
{
    case Ongoing = 'ongoing';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Ongoing => 'Berjalan',
            self::Paid => 'Lunas',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
