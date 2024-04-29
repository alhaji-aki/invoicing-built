<?php

namespace App\Jobs\Customer;

use App\Models\Customer;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CreatePaystackCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Customer $customer)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->customer->paystack_customer_code) {
            return;
        }

        $response = Http::paystack()
            ->post('customer', [
                'first_name' => $this->customer->name,
                'email' => $this->customer->email,
            ])->object();

        if (! $response->status) {
            throw new Exception("Could not create customer. Reason: {$response->message}");
        }

        $this->customer->update([
            'paystack_customer_code' => $response->data->customer_code,
        ]);
    }
}
