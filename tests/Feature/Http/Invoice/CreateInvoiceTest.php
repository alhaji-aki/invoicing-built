<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Faker\fake;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

it('unauthenticated users cannot create invoice', function () {
    postJson(route('invoices.store'))
        ->assertUnauthorized();
});

it('unverified users cannot create invoice', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->unverified()->create();

    actingAs($user)
        ->postJson(route('invoices.store'))
        ->assertForbidden()
        ->assertJson(['message' => 'Your email address is not verified.']);
});

it('creating invoice request is validated', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->create();

    actingAs($user)
        ->postJson(route('invoices.store'))
        ->assertInvalid(['customer.uuid', 'customer.name', 'due_at', 'items']);
});

it('creates and returns invoice in response', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $items = Product::factory()->for($user)
        ->count(3)
        ->state(fn () => ['quantity' => fake()->numberBetween(4, 100)])
        ->create()
        ->map(fn (Product $product) => [
            'product_id' => $product->uuid,
            'description' => fake()->sentence(),
            'quantity' => fake()->numberBetween(1, $product->quantity),
        ]);

    $data = [
        'customer' => [
            'uuid' => $customer->uuid,
        ],
        'issued_at' => now(),
        'due_at' => now()->addDays(30),
        'items' => $items,
    ];

    actingAs($user)
        ->postJson(route('invoices.store'), $data)
        ->assertSuccessful()
        ->assertJsonStructure(['message', 'data'])
        ->assertJson(function (AssertableJson $json) {
            $json->has('message')
                ->has('data', function (AssertableJson $json) {
                    $json->hasAll([
                        'uuid', 'invoice_no', 'amount', 'issued_at', 'due_at',
                        'created_at', 'customer', 'invoice_items',
                    ])->has('invoice_items', 3);
                });
        });
});
