<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\QuoteResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuotesRelationManager extends RelationManager
{
    protected static string $relationship = 'quotes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_name')->label('Servicio'),
                Tables\Columns\TextColumn::make('request_number')->label('N° Solicitud'),
                Tables\Columns\TextColumn::make('status')->label('Estado'),
                Tables\Columns\TextColumn::make('quote_date')->label('Fecha Cotización')->date('d/m/Y'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('crear_cotizacion')
                    ->label('Nueva Cotización')
                    ->icon('heroicon-o-plus')
                    ->url(fn() => QuoteResource::getUrl('create', [
                        'project_id' => $this->getOwnerRecord()->id,
                        'sub_client_id' => $this->getOwnerRecord()->sub_client_id,
                        'service_code' => $this->getOwnerRecord()->service_code,
                    ]))->openUrlInNewTab(),
            ])
            ->actions([
                // Puedes agregar acciones personalizadas aquí si lo deseas
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
