<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            // Unidades Dimensionales (FISICA)
            ['name' => 'Unidad', 'symbol' => 'UND', 'category' => 'FISICA'],
            ['name' => 'Metro Lineal', 'symbol' => 'ML', 'category' => 'FISICA'],
            ['name' => 'Metro Cuadrado', 'symbol' => 'M2', 'category' => 'FISICA'],
            ['name' => 'Metro Cúbico', 'symbol' => 'M3', 'category' => 'FISICA'],

            // Unidades de Servicio (SERVICIO)
            ['name' => 'Global', 'symbol' => 'GLB', 'category' => 'SERVICIO'],
            ['name' => 'Sede', 'symbol' => 'SEDE', 'category' => 'SERVICIO'],
            ['name' => 'Tienda', 'symbol' => 'TIENDA', 'category' => 'SERVICIO'],
            ['name' => 'Por Visita / Sede', 'symbol' => 'POR VISITA / SEDE', 'category' => 'SERVICIO'],
            ['name' => 'Evento / Mes', 'symbol' => 'EVENTO / MES', 'category' => 'SERVICIO'],

            // Unidades Compuestas (TIEMPO)
            ['name' => 'Día', 'symbol' => 'DIA', 'category' => 'TIEMPO'],
            ['name' => 'Por Persona', 'symbol' => 'POR PERSONA', 'category' => 'TIEMPO'],
            ['name' => 'Por Persona x Día', 'symbol' => 'POR PERSONA X DIA', 'category' => 'TIEMPO'],
            ['name' => 'Por Ruta / Día / Sede', 'symbol' => 'POR RUTA / DIA / SEDE', 'category' => 'TIEMPO'],
            ['name' => 'Por Ruta x Sede x Día', 'symbol' => 'POR RUTA X SEDE X DIA', 'category' => 'TIEMPO'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
