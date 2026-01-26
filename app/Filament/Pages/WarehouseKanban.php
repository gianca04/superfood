<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class WarehouseKanban extends Page
{
    protected static ?string $navigationIcon = null; // Hidden
    protected static ?string $navigationLabel = 'Tablero Almacén (Legacy)';
    protected static ?string $title = 'Kanban de Atención de Almacén';
    protected static ?string $slug = 'warehouse-kanban-legacy';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.warehouse-kanban';

    // Method removed as logic moved to WarehouseStatusController
}
