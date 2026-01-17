<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use App\Models\Quote;
use Filament\Resources\Pages\Page;

class EditQuote extends Page
{
    protected static string $resource = QuoteResource::class;

    protected static string $view = 'filament.resources.quote-resource.pages.manage-quote';

    protected static ?string $title = 'Editar Cotización';

    public Quote $record;

    public function mount(int | string $record): void
    {
        $this->record = Quote::findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Editar Cotización #' . $this->record->id;
    }
}
