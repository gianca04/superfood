<?php

namespace App\Filament\Resources\QuoteWarehouseResource\Pages;

use App\Filament\Resources\QuoteWarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuoteWarehouses extends ListRecords
{
    protected static string $resource = QuoteWarehouseResource::class;

    protected static string $view = 'filament.pages.warehouse-kanban';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }

    public function getViewData(): array
    {
        $statuses = [
            'pending' => 'Pendiente',
            'partial' => 'Parcial',
            'attended' => 'Atendido',
        ];

        // Obtener todos los registros de quote_warehouse relacionados a cotizaciones aprobadas
        $quoteWarehouses = \App\Models\QuoteWarehouse::with(['quote.subClient'])
            ->whereHas('quote', function ($q) {
                $q->where('status', 'Aprobado');
            })
            ->get();

        // Agrupar los registros de quote_warehouse por su status
        $kanbanData = [];
        foreach ($statuses as $statusKey => $statusLabel) {
            $kanbanData[$statusKey] = $quoteWarehouses->filter(function ($qw) use ($statusKey) {
                // Filtrar registros que coincidan con el estado en inglés o español
                return in_array($qw->status, [$statusKey, ucfirst($statusKey), $this->getSpanishStatus($statusKey)]);
            });
        }

        return [
            'statuses' => $statuses,
            'kanbanData' => $kanbanData,
        ];
    }

    private function getSpanishStatus(string $statusKey): string
    {
        return match ($statusKey) {
            'pending' => 'Pendiente',
            'partial' => 'Parcial',
            'attended' => 'Atendido',
            default => $statusKey,
        };
    }
}
