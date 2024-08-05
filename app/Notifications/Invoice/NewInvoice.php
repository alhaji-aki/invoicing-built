<?php

namespace App\Notifications\Invoice;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInvoice extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Invoice $invoice) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Invoice from {$this->invoice->user?->name}!")
            ->line("You have a new invoice from {$this->invoice->user?->name}.")
            ->line("Invoice No.: {$this->invoice->invoice_no}")
            ->line("Total Amount: {$this->invoice->formatted_amount}")
            ->line("Amount Paid: {$this->invoice->formatted_amount_paid}")
            ->line("Amount Owing: {$this->invoice->formatted_amount_owing}")
            ->action('Click to pay', $this->invoice->payment_link);
    }
}
