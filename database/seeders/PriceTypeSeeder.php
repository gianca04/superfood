<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PriceType;

class PriceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priceTypes = [
            'Mantenimiento Preventivo Baja Tensión',
            'Mantenimiento Correctivos Baja Tensión',
            'Viaticos correctivos Baja Tensión',
        ];

        foreach ($priceTypes as $name) {
            PriceType::create(['name' => $name]);
        }
    }
}
