<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Quote;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Lógica para la cotización (Quote)
        if (session()->has('quote_id')) {
            $data['quote_id'] = session('quote_id');
        }

        // 2. Lógica para el creador (Employee)
        $user = Auth::user();

        if ($user && $user->employee_id) {
            $data['employee_id'] = $user->employee_id;
        }

        return $data;
    }
    protected function getFormSchema(): array
    {
        $quoteId = session('quote_id');

        $fields = [
            TextInput::make('name')
                ->maxLength(255)
                ->required(),
            DatePicker::make('start_date'),
            DatePicker::make('end_date'),
            TextInput::make('location')
                ->maxLength(255),
            TextInput::make('latitude')
                ->numeric(),
            TextInput::make('longitude')
                ->numeric(),
            TextInput::make('photo')
                ->maxLength(255),
        ];

        // Si hay quote_id en sesión, lo mostramos como campo oculto y deshabilitado
        if ($quoteId) {
            array_unshift(
                $fields,
                Hidden::make('quote_id')->default($quoteId),
                Select::make('quote_id')
                    ->label('Cotización')
                    ->options(Quote::pluck('correlative', 'id'))
                    ->default($quoteId)
                    ->disabled()
            );
        } else {
            // Si no hay quote_id, permitimos seleccionar una cotización
            array_unshift(
                $fields,
                Select::make('quote_id')
                    ->label('Cotización')
                    ->options(Quote::pluck('correlative', 'id'))
                    ->required()
            );
        }

        return $fields;
    }
}
