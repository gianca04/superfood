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
    public function updated(Quote $quote): void
    {
        if ($quote->isDirty('status') && $quote->status === 'APROBADO') {
            \App\Models\QuoteWarehouse::firstOrCreate(
                ['quote_id' => $quote->id],
                ['status' => 'pending']
            );
        }
    }

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
