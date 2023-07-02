<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
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
    public function view(User $user, Customer $customer): Response|bool
    {
        if ($customer->user()->isNot($user)) {
            return Response::denyAsNotFound('Customer not found.');
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
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): Response|bool
    {
        if ($customer->user()->isNot($user)) {
            return Response::denyAsNotFound('Customer not found.');
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): Response|bool
    {
        if ($customer->user()->isNot($user)) {
            return Response::denyAsNotFound('Customer not found.');
        }

        return true;
    }
}
