<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use App\Models\Client;
use App\Models\PriceType;
use App\Models\Quote;
use App\Models\QuoteCategory;
use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class EditQuote extends Page
{
    protected static string $resource = QuoteResource::class;

    protected static string $view = 'filament.resources.quote-resource.pages.manage-quote';

    protected static ?string $title = 'Editar Cotización';

    public $record = null;

    private Quote $quoteRecord;

    // Datos pasados a la vista desde PHP
    public Collection $quoteCategories;
    public Collection $clients;
    public Collection $priceTypes;
    public ?string $projectUrl = null;

    public function mount(int | string $record): void
    {
        \Illuminate\Support\Facades\Log::info('EditQuote mount reached for record: ' . $record);
        // dd('EditQuote mount reached', $record);
        $this->quoteRecord = Quote::with([
            'subClient',
            'quoteDetails' => function ($query) {
                $query->orderBy('line', 'asc');
            },
            'quoteDetails.pricelist.unit',
            'quoteDetails.pricelist.priceType'
        ])->findOrFail($record);

        if ($this->quoteRecord->project_id) {
            $this->projectUrl = ProjectResource::getUrl('edit', ['record' => $this->quoteRecord->project_id]);
        }


        $this->record = $this->quoteRecord->toArray();

        // Cargar datos desde los modelos PHP directamente
        $this->quoteCategories = QuoteCategory::select('id', 'name')->orderBy('name')->get();
        $this->clients = Client::select('id', 'business_name', 'document_number')
            ->orderBy('business_name')
            ->get();
        $this->priceTypes = PriceType::select('id', 'name')->orderBy('id')->get();
    }

    public function getTitle(): string
    {
        return 'Editar Cotización #' . $this->quoteRecord->id;
    }
}
