<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pricelist extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sat_line',
        'sat_description',
        'unit_id',
        'unit_price',
        'price_type_id',
    ];

    /**
     * Get the unit that owns the pricelist.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the price type that owns the pricelist.
     */
    public function priceType(): BelongsTo
    {
        return $this->belongsTo(PriceType::class);
    }
    public function quoteDetails(): HasMany
    {
        return $this->hasMany(QuoteDetail::class);
    }
}
