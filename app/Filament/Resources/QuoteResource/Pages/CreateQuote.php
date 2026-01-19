<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use App\Models\Client;
use App\Models\PriceType;
use App\Models\QuoteCategory;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class CreateQuote extends Page
{
    protected static string $resource = QuoteResource::class;

    protected static string $view = 'filament.resources.quote-resource.pages.manage-quote';

    protected static ?string $title = 'Nueva Cotización';

    public ?array $record = null;

    // Datos pasados a la vista desde PHP
    public Collection $quoteCategories;
    public Collection $clients;
    public Collection $priceTypes;

    public function mount(): void
    {
        $this->record = null;
        
        // Cargar datos desde los modelos PHP directamente
        $this->quoteCategories = QuoteCategory::select('id', 'name')->orderBy('name')->get();
        $this->clients = Client::select('id', 'business_name', 'document_number')
            ->orderBy('business_name')
            ->get();
        $this->priceTypes = PriceType::select('id', 'name')->orderBy('id')->get();
    }
}
