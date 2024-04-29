<?php

namespace App\Jobs\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Notifications\Invoice\PaymentReceived;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateInvoiceAmount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var \App\Models\Invoice */
        $invoice = Invoice::query()->with(['customer'])->where('request_code', $this->data['request_code'])->firstOrNew();

        if (! $invoice->exists) {
            return;
        }

        if ($invoice->status === InvoiceStatus::PAID) {
            return;
        }

        $invoice->update([
            'amount_paid' => ($invoice->amount_paid + $this->data['amount']) / 100,
        ]);

        $invoice->customer?->notify(new PaymentReceived($invoice, $this->data['amount'] / 100));
    }
}
