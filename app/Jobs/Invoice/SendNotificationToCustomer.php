<?php

namespace App\Jobs\Invoice;

use App\Models\Invoice;
use App\Notifications\Invoice\NewInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationToCustomer implements ShouldQueue
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
        $this->invoice->customer?->notify(new NewInvoice($this->invoice));
    }
}
