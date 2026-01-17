<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceType extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the pricelists for this price type.
     */
    public function pricelists(): HasMany
    {
        return $this->hasMany(Pricelist::class);
    }
}
