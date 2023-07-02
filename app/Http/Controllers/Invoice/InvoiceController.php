<?php

namespace App\Http\Controllers\Invoice;

use App\Actions\Invoice\CreateInvoiceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Stringable;

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
    public function index(Request $request): Responsable
    {
        /** @var \App\Models\User */
        $user = $request->user();

        $invoices = $user->invoices()
            ->getQuery()
            ->with(['customer'])
            ->when($request->filled('query'), function (Builder $query) use ($request) {
                $searchTerm = $request->string('query')
                    ->trim()
                    ->tap(function (Stringable $value) {
                        abort_if($value->length() < 3, 400, 'The search term should be 3 or more characters');
                    })
                    ->pipe('htmlspecialchars')
                    ->toString();

                return $query->where('invoice_no', $searchTerm);
            })
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $states = ['past_due', 'pending'];

                $state = $request->string('status')->toString();

                abort_if(!in_array($state, $states), 400, 'The status submitted is invalid.');

                return match ($state) {
                    'pending' => $query->where('due_at', '>', now()),
                    'past_due' => $query->where('due_at', '<=', now()),
                    default => $query
                };
            })
            ->paginate()
            ->withQueryString();

        return InvoiceResource::collection($invoices)->additional(['message' => 'Get invoices.']);
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
    public function show(Invoice $invoice): Responsable
    {
        return (new InvoiceResource(
            $invoice->load(['customer', 'invoiceItems' => ['product']])
        ))->additional(['message' => 'Get invoice.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // TODO:
    }
}
