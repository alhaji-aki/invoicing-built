<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Customer\FirstOrCreateCustomerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Stringable;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified']);

        $this->authorizeResource(Customer::class, 'customer');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Responsable
    {
        /** @var \App\Models\User */
        $user = $request->user();

        $customers = $user->customers()
            ->getQuery()
            ->when($request->filled('query'), function (Builder $query) use ($request) {
                $searchTerm = $request->string('query')
                    ->trim()
                    ->tap(function (Stringable $value) {
                        abort_if($value->length() < 3, 400, 'The search term should be 3 or more characters');
                    })
                    ->pipe('htmlspecialchars')
                    ->append('%')
                    ->prepend('%')
                    ->toString();

                return $query->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', $searchTerm)->orWhere('email', 'LIKE', $searchTerm);
                });
            })
            ->paginate()
            ->withQueryString();

        return CustomerResource::collection($customers)->additional(['message' => 'Get customers.']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request, FirstOrCreateCustomerAction $action): Responsable
    {
        /** @var \App\Models\User */
        $user = $request->user();

        $customer = $action->execute($user, (array) $request->validated());

        return (new CustomerResource($customer))->additional(['message' => 'Customer created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): Responsable
    {
        return (new CustomerResource($customer))->additional(['message' => 'Get customer.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): Responsable
    {
        $data = array_filter((array) $request->validated());

        abort_if(empty($data), 400, 'No data submitted.');

        $customer->update($data);

        return (new CustomerResource($customer->refresh()))->additional(['message' => 'Customer updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully.']);
    }
}
