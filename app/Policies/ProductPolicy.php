<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
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
    public function view(User $user, Product $product): Response|bool
    {
        if ($product->user()->isNot($user)) {
            return Response::denyAsNotFound('Product not found.');
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
    public function update(User $user, Product $product): Response|bool
    {
        if ($product->user()->isNot($user)) {
            return Response::denyAsNotFound('Product not found.');
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): Response|bool
    {
        if ($product->user()->isNot($user)) {
            return Response::denyAsNotFound('Product not found.');
        }

        return true;
    }
}
