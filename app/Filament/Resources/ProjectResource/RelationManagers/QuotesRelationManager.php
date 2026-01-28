<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\QuoteResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuotesRelationManager extends RelationManager
{
    protected static string $relationship = 'quotes';
    protected static ?string $title = 'Cotizaciones';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')->label('ID Cotización'),
                Tables\Columns\TextColumn::make('service_name')
                    ->label('Servicio')
                    ->getStateUsing(fn($record) => $record->project->name ?? ''),
                Tables\Columns\TextColumn::make('')
                    ->label('N° Solicitud')
                    ->getStateUsing(fn($record) => $record->project->request_number ?? ''),
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
                        'name' => $this->getOwnerRecord()->name,
                    ]))->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preview')
                        ->label('Previsualizar')
                        ->icon('heroicon-o-eye')
                        ->url(fn($record) => url("/quotes/{$record->id}/preview"))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('edit')
                        ->label('Editar')
                        ->icon('heroicon-o-pencil-square')
                        ->url(fn($record) => QuoteResource::getUrl('edit', ['record' => $record->id]))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('export_excel')
                        ->label('Exportar Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn($record) => url("/quotes/{$record->id}/excel"))
                        ->openUrlInNewTab(),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->tooltip('Opciones'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
