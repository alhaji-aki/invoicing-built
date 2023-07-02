<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\InvoiceItem */
class InvoiceItemResource extends JsonResource
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
            'description' => $this->description,
            'unit_price' => $this->formatted_unit_price,
            'quantity' => $this->quantity,
            'amount' => $this->formatted_amount,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
