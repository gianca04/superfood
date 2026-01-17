<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = ['name', 'symbol', 'category'];

    /**
     * Get the pricelists for the unit.
     */
    public function pricelists(): HasMany
    {
        return $this->hasMany(Pricelist::class);
    }
}
