<?php

namespace App\Http\Controllers\Invoice;

use App\Actions\Invoice\CreateInvoiceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Contracts\Support\Responsable;

class InvoiceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified']);

        $this->authorizeResource(Invoice::class, 'invoice');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO:
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request, CreateInvoiceAction $createInvoiceAction): Responsable
    {
        /** @var \App\Models\User */
        $user = $request->user();

        $invoice = $createInvoiceAction->execute($user, (array) $request->validated());

        return (new InvoiceResource($invoice))->additional(['message' => 'Invoice created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        // TODO:
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // TODO:
    }
}
