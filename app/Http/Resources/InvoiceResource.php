<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Invoice */
class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'invoice_no' => $this->invoice_no,
            'amount' => $this->formatted_amount,
            'issued_at' => $this->issued_at,
            'due_at' => $this->due_at,
            'created_at' => $this->created_at,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'invoice_items' => InvoiceItemResource::collection($this->whenLoaded('invoiceItems')),
        ];
    }
}
