<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class WarehouseKanban extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Tablero Almacén';
    protected static ?string $title = 'Kanban de Atención de Almacén';
    protected static ?string $slug = 'warehouse-kanban';

    protected static string $view = 'filament.pages.warehouse-kanban';

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

    // Method removed as logic moved to WarehouseStatusController
}
