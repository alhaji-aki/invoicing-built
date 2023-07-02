<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
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

    /** @return Attribute<never, int> */
    protected function price(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => intval($value * 100),
        );
    }

    /** @return Attribute<float, void> */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price / 100
        );
    }

    /** @return Attribute<bool, never> */
    protected function inStock(): Attribute
    {
        return Attribute::get(fn (): bool => $this->quantity > 0);
    }
}
