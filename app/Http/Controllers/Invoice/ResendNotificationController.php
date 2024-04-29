<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Jobs\Customer\CreatePaystackCustomer;
use App\Jobs\Invoice\GeneratePaymentLink;
use App\Jobs\Invoice\SendNotificationToCustomer;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class ResendNotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified', 'can:resend,invoice']);
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Invoice $invoice): JsonResponse
    {
        Bus::chain([
            new CreatePaystackCustomer($invoice->customer ?? new Customer()),
            new GeneratePaymentLink($invoice),
            new SendNotificationToCustomer($invoice),
        ])->dispatch();

        return response()->json([
            'message' => 'We are resending invoice to the customer.',
        ]);
    }
}
