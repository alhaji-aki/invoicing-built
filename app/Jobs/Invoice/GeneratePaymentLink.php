<?php

namespace App\Jobs\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GeneratePaymentLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Invoice $invoice)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->invoice->request_code) {
            return;
        }

        if ($this->invoice->status === InvoiceStatus::PAID) {
            return;
        }

        $items = $this->invoice->invoiceItems()
            ->getQuery()
            ->with(['product'])
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $lineItems = $items->map(function (InvoiceItem $invoiceItem) {
            return [
                'name' => $invoiceItem->product?->title,
                'amount' => $invoiceItem->amount,
            ];
        });

        $response = Http::paystack()->post('paymentrequest', [
            'description' => "Invoice from {$this->invoice->user?->name} with code {$this->invoice->invoice_no}.",
            'customer' => $this->invoice->customer?->paystack_customer_code,
            'due_date' => $this->invoice->due_at,
            'has_invoice' => true,
            'amount' => $this->invoice->amount,
            'line_items' => $lineItems,
            'send_notification' => false,
        ])->object();

        if (!$response->status) {
            throw new Exception("Could not create payment request. Reason: {$response->message}");
        }

        $this->invoice->update(['request_code' => $response->data->request_code]);
    }
}
