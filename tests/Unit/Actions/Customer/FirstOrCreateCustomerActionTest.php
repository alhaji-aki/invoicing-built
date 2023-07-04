<?php

namespace Tests\Unit\Actions\Customer;

use App\Actions\Customer\FirstOrCreateCustomerAction;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(
    TestCase::class,
    LazilyRefreshDatabase::class
);

beforeEach(function () {
    $this->firstOrCreateCustomerAction = app(FirstOrCreateCustomerAction::class);
});

it('returns a customer if the uuid is part of the data object', function () {
    $customer = Customer::factory()->create();
    $this->assertDatabaseCount('customers', 1);

    $returnedCustomer = $this->firstOrCreateCustomerAction->execute($customer->user, ['uuid' => $customer->uuid]);

    $this->assertTrue($customer->is($returnedCustomer));
});

it('creates a customer using the name and email in the object', function () {
    $user = User::factory()->create();

    $this->assertDatabaseCount('customers', 0);

    $data = ['name' => 'John Doe', 'email' => 'johndoe@example.com'];

    $this->firstOrCreateCustomerAction->execute($user, $data);

    $this->assertDatabaseCount('customers', 1);
    $this->assertDatabaseHas('customers', array_merge($data, ['user_id' => $user->id]));
});

it('throws a not found exception if customer with uuid does not exist', function () {
    $user = User::factory()->create();

    $this->assertDatabaseCount('customers', 0);

    $this->firstOrCreateCustomerAction->execute($user, ['uuid' => Str::uuid()]);
})->throws(ModelNotFoundException::class);

it('throws a not found exception if customer with uuid does not belong to the user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $this->assertDatabaseCount('customers', 1);

    $this->firstOrCreateCustomerAction->execute($user, ['uuid' => $customer->uuid]);
})->throws(ModelNotFoundException::class);
