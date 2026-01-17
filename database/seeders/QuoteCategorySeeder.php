<?php

namespace Database\Seeders;

use App\Models\QuoteCategory;
use Illuminate\Database\Seeder;

class QuoteCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Grupos electrógenos',
                'description' => 'Servicios relacionados con grupos electrógenos',
            ],
            [
                'name' => 'II.EE. Baja Tensión',
                'description' => 'Instalaciones eléctricas de baja tensión',
            ],
            [
                'name' => 'II.EE. Media Tensión',
                'description' => 'Instalaciones eléctricas de media tensión',
            ],
            [
                'name' => 'UPS',
                'description' => 'Sistemas de alimentación ininterrumpida',
            ],
            [
                'name' => 'Sistema Contraincendios',
                'description' => 'Sistemas de detección y extinción de incendios',
            ],
            [
                'name' => 'Extintores',
                'description' => 'Equipos extintores de incendios',
            ],
            [
                'name' => 'Puertas cortafuegos',
                'description' => 'Puertas resistentes al fuego',
            ],
        ];

        foreach ($categories as $category) {
            QuoteCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
