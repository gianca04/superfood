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
            Actions\CreateAction::make(),
        ];
    }

    public function getViewData(): array
    {
        $statuses = [
            'pending' => 'Pendiente',
            'partial' => 'Parcial',
            'attended' => 'Atendido',
        ];

        // Fetch all quotes that are APPROVED
        $quotes = \App\Models\Quote::query()
            ->where('status', 'APROBADO')
            ->with(['quoteWarehouse', 'subClient'])
            ->get();

        // Group quotes by their warehouse status
        // If no warehouse record exists, it defaults to 'pending'
        $kanbanData = [];
        foreach ($statuses as $statusKey => $statusLabel) {
            $kanbanData[$statusKey] = $quotes->filter(function ($quote) use ($statusKey) {
                $currentStatus = $quote->quoteWarehouse?->status ?? 'pending';
                return $currentStatus === $statusKey;
            });
        }

        return [
            'statuses' => $statuses,
            'kanbanData' => $kanbanData,
        ];
    }
}
