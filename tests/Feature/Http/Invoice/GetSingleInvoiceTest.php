<?php

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\{actingAs, getJson};

test('unauthenticated users cannot get invoice', function () {
    $invoice = Invoice::factory()->create();

    getJson(route('invoices.show', $invoice))
        ->assertUnauthorized();
});

test('unverified users cannot get invoice', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->unverified()->create();

    $invoice = Invoice::factory()->create();

    actingAs($user)
        ->getJson(route('invoices.show', $invoice))
        ->assertForbidden()
        ->assertJson(['message' => "Your email address is not verified."]);
});

test('users cannot get invoice that does not belong to them', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->create();

    $invoice = Invoice::factory()->create();

    actingAs($user)
        ->getJson(route('invoices.show', $invoice))
        ->assertNotFound();
});

test('user gets a single invoice', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->create();

    $invoice = Invoice::factory()->for($user)->create();

    actingAs($user)
        ->getJson(route('invoices.show', $invoice))
        ->assertSuccessful()
        ->assertJson(function (AssertableJson $json) {
            $json->has('message')
                ->has('data', function (AssertableJson $json) {
                    $json->hasAll([
                        'uuid', 'invoice_no', 'amount', 'issued_at', 'due_at',
                        'created_at', 'customer', 'invoice_items',
                    ]);
                });
        });
});
