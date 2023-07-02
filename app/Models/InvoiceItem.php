<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'unit_price',
        'quantity',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'unit_price' => 'integer',
        'quantity' => 'integer',
        'amount' => 'integer',
    ];

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['uuid'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return Attribute<never, int> */
    protected function unitPrice(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => intval($value * 100),
        );
    }

    /** @return Attribute<never, int> */
    protected function amount(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => intval($value * 100),
        );
    }

    /** @return Attribute<float, void> */
    protected function formattedUnitPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->unit_price / 100
        );
    }

    /** @return Attribute<float, void> */
    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount / 100
        );
    }
}
