<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
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
        'amount_paid',
        'request_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'amount_paid' => 'integer',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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

    /** @return Attribute<never, int> */
    protected function amountPaid(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => intval($value * 100),
        );
    }

    /** @return Attribute<float, void> */
    protected function formattedAmountPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount_paid / 100
        );
    }

    /** @return Attribute<int, never> */
    protected function amountOwing(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $this->amount - $this->amount_paid,
        );
    }

    /** @return Attribute<float, void> */
    protected function formattedAmountOwing(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount_owing / 100
        );
    }

    /** @return Attribute<InvoiceStatus, never> */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                if ($this->amount_owing == $this->amount) {
                    return InvoiceStatus::PENDING;
                }

                if ($this->amount_owing <= 0) {
                    return InvoiceStatus::PAID;
                }

                return InvoiceStatus::PARTLY_PAID;
            },
        );
    }

    /** @return \Illuminate\Database\Eloquent\Casts\Attribute<string, never> */
    protected function paymentLink(): Attribute
    {
        return Attribute::make(
            // @phpstan-ignore-next-line
            get: function ($value, array $attributes): ?string {
                if ($this->request_code) {
                    return config('paystack.payment_url').'/'.$this->request_code;
                }

                return null;
            }
        );
    }
}
