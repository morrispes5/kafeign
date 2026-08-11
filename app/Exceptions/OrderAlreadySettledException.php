<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when Admin\OrderController::clear()'s or cancel()'s conditional
 * UPDATE (`where('status', 'ongoing')`) affects zero rows — another
 * request already transitioned this order out of `ongoing` between the
 * controller's pre-check and this atomic update (a double form submit,
 * or two admin tabs open on the same order). Both callers catch this and
 * show the exact same "sudah tidak aktif" message the pre-check already
 * shows for the non-race case, so a lost race is invisible to the
 * cashier as anything other than "someone already handled this".
 *
 * Always thrown from inside the settlement DB::transaction(), so
 * whatever else that transaction already did (minting a receipt number,
 * restoring stock) rolls back with it — a lost race never has a
 * partial, half-applied side effect.
 */
class OrderAlreadySettledException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Pesanan ini sudah tidak aktif.');
    }
}
