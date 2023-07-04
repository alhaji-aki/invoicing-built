<?php

use App\Models\Invoice;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;

it('unauthenticated users cannot delete invoice', function () {
    $invoice = Invoice::factory()->create();

    deleteJson(route('invoices.destroy', $invoice))
        ->assertUnauthorized();
});

it('unverified users cannot delete invoice', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->unverified()->create();

    $invoice = Invoice::factory()->create();

    actingAs($user)
        ->deleteJson(route('invoices.destroy', $invoice))
        ->assertForbidden()
        ->assertJson(['message' => 'Your email address is not verified.']);
});

it('users cannot delete invoice that does not belong to them', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->create();

    $invoice = Invoice::factory()->create();

    actingAs($user)
        ->deleteJson(route('invoices.destroy', $invoice))
        ->assertNotFound();

    $this->assertModelExists($invoice);
});

it('deletes the invoice from the database', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $user = User::factory()->create();

    $invoice = Invoice::factory()->for($user)->create();

    actingAs($user)
        ->deleteJson(route('invoices.destroy', $invoice))
        ->assertSuccessful();

    $this->assertSoftDeleted($invoice);
});
