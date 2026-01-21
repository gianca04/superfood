<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use App\Models\Client;
use App\Models\PriceType;
use App\Models\QuoteCategory;
use App\Models\Project;
use Filament\Resources\Pages\Page;

use Filament\Resources\Pages\CreateRecord; // Si es para crear un registro
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
    public ?int $projectId = null;
    public ?int $subClientId = null;
    public ?string $serviceCode = null;
    public ?object $project = null;
    public ?string $suggestedRequestNumber = null;

    public function mount(): void
    {
        $this->record = null;

        // Cargar datos desde los modelos PHP directamente
        $this->quoteCategories = QuoteCategory::select('id', 'name')->orderBy('name')->get();
        $this->clients = Client::select('id', 'business_name', 'document_number')
            ->orderBy('business_name')
            ->get();
        $this->priceTypes = PriceType::select('id', 'name')->orderBy('id')->get();
        $projectId = request()->query('project_id');
        $this->project = $projectId ? Project::find($projectId) : null;
        $this->subClientId = request()->query('sub_client_id');
        $this->serviceCode = request()->query('service_code');
        // Generar el número sugerido solo si hay proyecto
        $this->suggestedRequestNumber = $projectId ? \App\Models\Quote::generateNextRequestNumber($projectId) : null;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($projectId = request()->query('project_id')) {
            $data['project_id'] = $projectId;
        }
        return $data;
    }
}
