<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'Airis',
            'Belden',
            'Bremas',
            'BTicino / Ticino',
            'Camsco',
            'Circutor',
            'Dairu',
            'Dixon Lighting',
            'Efapel / Efaphel',
            'Elcope',
            'Elise',
            'Indeco',
            'Ledvance',
            'Legrand',
            'Levinton',
            'Mennekes / Meneke',
            'Misol',
            'Opalux',
            'Panduit',
            'Philips / Phillips',
            'Rawelt',
            'Schneider / Scheneider Electric',
            'Siemens',
            'Sinotimer',
            'Sonoff',
            'Talma',
            'Thor Gel',
            'Tuya / Tuya Smart',
            'Wavlink',
        ];

        foreach ($brands as $brandName) {
            Brand::create(['name' => $brandName]);
        }
    }
}
