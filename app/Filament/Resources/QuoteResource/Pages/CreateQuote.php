<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use Filament\Resources\Pages\Page;

class CreateQuote extends Page
{
    protected static string $resource = QuoteResource::class;

    protected static string $view = 'filament.resources.quote-resource.pages.manage-quote';

    protected static ?string $title = 'Nueva Cotización';

    public ?array $record = null;

    public function mount(): void
    {
        $this->record = null;
    }
}
