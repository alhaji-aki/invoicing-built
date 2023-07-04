<?php

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('unauthenticated users cannot get invoices', function () {
    getJson(route('invoices.index'))
        ->assertUnauthorized();
});

test('unverified users cannot get invoices', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->unverified()->create();

    actingAs($user)
        ->getJson(route('invoices.index'))
        ->assertForbidden()
        ->assertJson(['message' => 'Your email address is not verified.']);
});

test('users will only see invoices belonging to them', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->create();

    Invoice::factory()->for($user)->create();
    $otherUsersInvoice = Invoice::factory()->create();

    actingAs($user)
        ->getJson(route('invoices.index'))
        ->assertSuccessful()
        ->assertJson(function (AssertableJson $json) {
            $json->hasAll(['message', 'meta', 'links'])
                ->has('data', 1, function (AssertableJson $json) {
                    $json->hasAll([
                        'uuid', 'invoice_no', 'amount', 'issued_at', 'due_at',
                        'created_at', 'customer',
                    ]);
                });
        })->assertJsonMissing(['uuid' => $otherUsersInvoice->uuid]);
});

test('user can search by an invoice number', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->create();

    $invoice = Invoice::factory()->for($user)->create();
    $otherInvoice = Invoice::factory()->for($user)->create();

    actingAs($user)
        ->getJson(route('invoices.index', ['query' => $invoice->invoice_no]))
        ->assertSuccessful()
        ->assertJsonFragment(['uuid' => $invoice->uuid])
        ->assertJsonMissing(['uuid' => $otherInvoice->uuid]);
});

test('user can filter by a status', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->create();

    $pastInvoice = Invoice::factory()->for($user)->create([
        'issued_at' => now()->subMonth(),
        'due_at' => now()->subMonth()->addDays(15),
    ]);
    $pendingInvoice = Invoice::factory()->for($user)->create([
        'issued_at' => now(),
        'due_at' => now()->addDays(15),
    ]);

    actingAs($user)
        ->getJson(route('invoices.index', ['status' => 'past_due']))
        ->assertSuccessful()
        ->assertJsonFragment(['uuid' => $pastInvoice->uuid])
        ->assertJsonMissing(['uuid' => $pendingInvoice->uuid]);

    actingAs($user)
        ->getJson(route('invoices.index', ['status' => 'pending']))
        ->assertSuccessful()
        ->assertJsonFragment(['uuid' => $pendingInvoice->uuid])
        ->assertJsonMissing(['uuid' => $pastInvoice->uuid]);

    actingAs($user)
        ->getJson(route('invoices.index', ['status' => 'invalid']))
        ->assertBadRequest()
        ->assertJson(['message' => 'The status submitted is invalid.'])
        ->assertJsonMissing(['uuid' => $pendingInvoice->uuid])
        ->assertJsonMissing(['uuid' => $pastInvoice->uuid]);
});
