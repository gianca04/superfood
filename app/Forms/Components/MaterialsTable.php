<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class MaterialsTable extends Field
{
    protected string $view = 'forms.components.materials-table';

    protected function setUp(): void
    {
        parent::setUp();

        // Valor por defecto
        $this->default([]);

        $this->dehydrateStateUsing(function ($state) {
            // Si es string JSON, decodificar
            if (is_string($state) && !empty($state)) {
                $decoded = json_decode($state, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }

            // Si es array, retornar directamente
            if (is_array($state)) {
                // Filtrar filas vacías
                return array_values(array_filter($state, function ($row) {
                    if (!is_array($row)) return false;
                    $material = trim($row['material'] ?? '');
                    $unidad = trim($row['unidad'] ?? '');
                    $cantidad = trim($row['cantidad'] ?? '');
                    return $material !== '' || $unidad !== '' || $cantidad !== '';
                }));
            }

            return [];
        });

        $this->afterStateHydrated(function ($component, $state) {
            // Si es string JSON, decodificar
            if (is_string($state) && !empty($state)) {
                $decoded = json_decode($state, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $component->state($decoded);
                    return;
                }
            }

            // Si ya es array, usarlo
            if (is_array($state)) {
                $component->state($state);
                return;
            }

            // Por defecto, array vacío
            $component->state([]);
        });
    }
}
