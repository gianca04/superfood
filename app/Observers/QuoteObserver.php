<?php

namespace App\Observers;

use App\Models\Quote;

class QuoteObserver
{
    /**
     * Handle the Quote "created" event.
     */
    public function created(Quote $quote): void
    {
        //
    }

    /**
     * Handle the Quote "updated" event.
     */

    /**
     * Handle the Quote "deleted" event.
     */
    public function deleted(Quote $quote): void
    {
        //
    }

    /**
     * Handle the Quote "restored" event.
     */
    public function restored(Quote $quote): void
    {
        //
    }

    /**
     * Handle the Quote "force deleted" event.
     */
    public function forceDeleted(Quote $quote): void
    {
        //
    }
}
