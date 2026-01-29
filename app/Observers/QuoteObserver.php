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
        if ($quote->isDirty('status') && $quote->status === 'Aprobado') {
            // Anular otras cotizaciones aprobadas del mismo proyecto
            Quote::where('project_id', $quote->project_id)
                ->where('id', '!=', $quote->id)
                ->where('status', 'Aprobado')
                ->update(['status' => 'Anulado']);

            // Actualizar fecha de aprobación en el proyecto
            if ($quote->project) {
                $quote->project->update(['quote_approved_at' => now()]);
            }
        }

        if ($quote->isDirty('status') && $quote->status === 'Enviado') {
            // Actualizar fecha de envío en el proyecto
            if ($quote->project) {
                $quote->project->update(['quote_sent_at' => now()]);
            }
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
