<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'invoice_no',
        'amount',
        'issued_at',
        'due_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $invoice) {
            $invoice->invoice_no = $invoice->generateInvoiceNo();
        });
    }

    public function generateInvoiceNo(): string
    {
        $invoiceNo = 'INV-'.rand(10000000, 99999999);

        while (
            self::query()
                ->where('invoice_no', $invoiceNo)
                ->exists()
        ) {
            $this->generateInvoiceNo();
        }

        return $invoiceNo;
    }

    /** @return Attribute<never, int> */
    protected function amount(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => intval($value * 100),
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
