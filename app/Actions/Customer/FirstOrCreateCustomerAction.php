<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Arr;

class FirstOrCreateCustomerAction
{
    public function execute(User $user, array $data): Customer
    {
        if (isset($data['uuid'])) {
            return Customer::query()
                ->where('user_id', $user->id)
                ->where('uuid', $data['uuid'])
                ->firstOrFail();
        }

        return $user->customers()->create(Arr::except($data, 'uuid'))->refresh();
    }
}
