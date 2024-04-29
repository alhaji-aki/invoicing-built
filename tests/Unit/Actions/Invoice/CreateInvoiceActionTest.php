<?php

namespace Tests\Unit\Actions\Invoice;

use App\Actions\Invoice\CreateInvoiceAction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

use function Pest\Faker\fake;

uses(
    TestCase::class,
    LazilyRefreshDatabase::class
);

beforeEach(function () {
    $this->createInvoiceAction = app(CreateInvoiceAction::class);
});

it('throws validation exception if a product in the items does not exist', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();

    $this->assertDatabaseCount('invoices', 0);
    $this->assertDatabaseCount('invoice_items', 0);

    $data = [
        'customer' => [
            'uuid' => $customer->uuid,
        ],
        'issued_at' => now(),
        'due_at' => now()->addDays(30),
        'items' => [[
            'product_id' => fake()->uuid(),
            'description' => fake()->sentence(),
            'quantity' => 1,
        ]],
    ];

    $this->createInvoiceAction->execute($user, $data);

    $this->assertDatabaseCount('invoices', 0);
    $this->assertDatabaseCount('invoice_items', 0);
})->throws(ValidationException::class);

it('throws validation exception if a product in the items does not belong to the user', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $products = Product::factory()->count(1)->create();

    $this->assertDatabaseCount('invoices', 0);
    $this->assertDatabaseCount('invoice_items', 0);

    $items = $products->map(fn (Product $product) => [
        'product_id' => $product->uuid,
        'description' => fake()->sentence(),
        'quantity' => $product->quantity + 1,
    ]);

    $data = [
        'customer' => [
            'uuid' => $customer->uuid,
        ],
        'issued_at' => now(),
        'due_at' => now()->addDays(30),
        'items' => $items->toArray(),
    ];

    $this->createInvoiceAction->execute($user, $data);

    $this->assertDatabaseCount('invoices', 0);
    $this->assertDatabaseCount('invoice_items', 0);
})->throws(ValidationException::class);

it('throws validation exception if a product in the items does not have the enough stock available to satisfy the invoice request', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $products = Product::factory()->for($user)
        ->count(1)
        ->create();

    $this->assertDatabaseCount('invoices', 0);
    $this->assertDatabaseCount('invoice_items', 0);

    $items = $products->map(fn (Product $product) => [
        'product_id' => $product->uuid,
        'description' => fake()->sentence(),
        'quantity' => $product->quantity + 1,
    ]);

    $data = [
        'customer' => [
            'uuid' => $customer->uuid,
        ],
        'issued_at' => now(),
        'due_at' => now()->addDays(30),
        'items' => $items->toArray(),
    ];

    $this->createInvoiceAction->execute($user, $data);

    $this->assertDatabaseCount('invoices', 0);
    $this->assertDatabaseCount('invoice_items', 0);
})->throws(ValidationException::class);

it('creates an invoice for a user given the required data', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create();
    $products = Product::factory()->for($user)
        ->count(3)
        ->state(fn () => ['quantity' => fake()->numberBetween(4, 100)])
        ->create();

    $this->assertDatabaseCount('invoices', 0);
    $this->assertDatabaseCount('invoice_items', 0);

    $items = $products->map(fn (Product $product) => [
        'product' => $product,
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
        'items' => $items->map(fn ($item) => Arr::except($item, 'product'))->toArray(),
    ];

    $invoice = $this->createInvoiceAction->execute($user, $data);

    $this->assertDatabaseCount('invoices', 1);
    $this->assertDatabaseCount('invoice_items', $items->count());

    $this->assertDatabaseHas('invoices', [
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'amount' => $items->sum(fn ($item) => $item['product']->price * $item['quantity']),
        'issued_at' => $data['issued_at'],
        'due_at' => $data['due_at'],
    ]);

    $items->each(function ($item) use ($invoice) {
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'product_id' => $item['product']->id,
            'description' => $item['description'],
            'unit_price' => $item['product']->price,
            'quantity' => $item['quantity'],
            'amount' => $item['product']->price * $item['quantity'],
        ]);
    });
});

it('creates a customer if the name and email of a customer is provided', function () {
    $user = User::factory()->create();
    $products = Product::factory()->for($user)
        ->count(3)
        ->state(fn () => ['quantity' => fake()->numberBetween(4, 100)])
        ->create();

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('invoices', 0);
    $this->assertDatabaseCount('invoice_items', 0);

    $items = $products->map(fn (Product $product) => [
        'product_id' => $product->uuid,
        'description' => fake()->sentence(),
        'quantity' => fake()->numberBetween(1, $product->quantity),
    ]);

    $data = [
        'customer' => ['name' => 'John Doe', 'email' => 'johndoe@example.com'],
        'issued_at' => now(),
        'due_at' => now()->addDays(30),
        'items' => $items->map(fn ($item) => Arr::except($item, 'product'))->toArray(),
    ];

    $this->createInvoiceAction->execute($user, $data);

    $this->assertDatabaseCount('customers', 1);
    $this->assertDatabaseCount('invoices', 1);
    $this->assertDatabaseCount('invoice_items', $items->count());

    $this->assertDatabaseHas('customers', array_merge($data['customer'], ['user_id' => $user->id]));
});
