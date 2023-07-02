<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): Response|bool
    {
        if ($invoice->user()->isNot($user)) {
            return Response::denyAsNotFound('Invoice not found.');
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): Response|bool
    {
        if ($invoice->user()->isNot($user)) {
            return Response::denyAsNotFound('Invoice not found.');
        }

        return true;
    }
}
