<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pricelist;
use App\Models\Unit;
use App\Models\PriceType;

class PricelistViaticosBTSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el tipo de precio "Viaticos correctivos Baja Tensión"
        $priceType = PriceType::firstOrCreate(['name' => 'Viaticos correctivos Baja Tensión']);

        // Datos de Viaticos correctivos Baja Tensión
        $pricelists = [
            // Viáticos Terrestres
            ['sat_line' => 'VTBT01', 'sat_description' => 'VIATICO CORRECTIVO LIMA ( CAÑETE, MALA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT02', 'sat_description' => 'VIATICO CORRECTIVO SUR CHICO ( ASIA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT03', 'sat_description' => 'VIATICO CORRECTIVO CENTRO ( HUANCAYO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT04', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( CAJAMARCA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 261.00],
            ['sat_line' => 'VTBT05', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( TRUJILLO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 190.00],
            ['sat_line' => 'VTBT06', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( CHICLAYO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 177.83],
            ['sat_line' => 'VTBT07', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( CHIMBOTE) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 202.00],
            ['sat_line' => 'VTBT08', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( PIURA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 165.00],
            ['sat_line' => 'VTBT09', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( JAÉN) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 206.37],
            ['sat_line' => 'VTBT10', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( PAITA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 165.00],
            ['sat_line' => 'VTBT11', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( SULLANA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 165.00],
            ['sat_line' => 'VTBT12', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( TALARA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 270.00],
            ['sat_line' => 'VTBT13', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( TUMBES) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => 233.28],
            ['sat_line' => 'VTBT14', 'sat_description' => 'VIATICO CORRECTIVO NORTE CHICO ( BARRANCA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT15', 'sat_description' => 'VIATICO CORRECTIVO NORTE CHICO ( HUACHO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT16', 'sat_description' => 'VIATICO CORRECTIVO NORTE CHICO ( HUARAL) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT17', 'sat_description' => 'VIATICO CORRECTIVO ORIENTE ( HUÁNUCO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT18', 'sat_description' => 'VIATICO CORRECTIVO ORIENTE ( PUCALLPA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT19', 'sat_description' => 'VIATICO CORRECTIVO ORIENTE ( TARAPOTO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT20', 'sat_description' => 'VIATICO CORRECTIVO SUR ( AREQUIPA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT21', 'sat_description' => 'VIATICO CORRECTIVO SUR ( CUSCO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT22', 'sat_description' => 'VIATICO CORRECTIVO SUR ( ILO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT23', 'sat_description' => 'VIATICO CORRECTIVO SUR ( JULIACA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT24', 'sat_description' => 'VIATICO CORRECTIVO SUR ( MOQUEGUA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT25', 'sat_description' => 'VIATICO CORRECTIVO SUR ( PUNO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT26', 'sat_description' => 'VIATICO CORRECTIVO SUR ( TACNA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT27', 'sat_description' => 'VIATICO CORRECTIVO SUR CHICO ( CHINCHA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT28', 'sat_description' => 'VIATICO CORRECTIVO SUR CHICO ( ICA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VTBT29', 'sat_description' => 'VIATICO CORRECTIVO SUR CHICO ( PISCO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V TERRESTRE', 'unit' => 'POR PERSONA', 'unit_price' => null],

            // Viáticos Aéreos
            ['sat_line' => 'VABT01', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( CAJAMARCA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT02', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( TRUJILLO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT03', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( CHICLAYO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT04', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( CHIMBOTE) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT05', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( PIURA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT06', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( JAÉN) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT07', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( PAITA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT08', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( SULLANA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT09', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( TALARA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT10', 'sat_description' => 'VIATICO CORRECTIVO NORTE ( TUMBES) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT11', 'sat_description' => 'VIATICO CORRECTIVO ORIENTE ( HUÁNUCO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT12', 'sat_description' => 'VIATICO CORRECTIVO ORIENTE ( PUCALLPA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT13', 'sat_description' => 'VIATICO CORRECTIVO ORIENTE ( TARAPOTO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT14', 'sat_description' => 'VIATICO CORRECTIVO SUR ( AREQUIPA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT15', 'sat_description' => 'VIATICO CORRECTIVO SUR ( CUSCO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT16', 'sat_description' => 'VIATICO CORRECTIVO SUR ( ILO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT17', 'sat_description' => 'VIATICO CORRECTIVO SUR ( JULIACA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT18', 'sat_description' => 'VIATICO CORRECTIVO SUR ( MOQUEGUA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT19', 'sat_description' => 'VIATICO CORRECTIVO SUR ( PUNO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT20', 'sat_description' => 'VIATICO CORRECTIVO SUR ( TACNA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V AÉREO', 'unit' => 'POR PERSONA', 'unit_price' => null],
            ['sat_line' => 'VABT21', 'sat_description' => 'VIATICO CORRECTIVO DIA ADICIONAL (ALOJAMIENTO Y HOSPEDAJE)', 'unit' => 'POR PERSONA X DIA', 'unit_price' => 83.00],
        ];

        foreach ($pricelists as $item) {
            $unit = Unit::firstOrCreate(
                ['symbol' => $item['unit']],
                ['name' => $item['unit'], 'category' => 'SERVICIO']
            );

            Pricelist::create([
                'sat_line' => $item['sat_line'],
                'sat_description' => $item['sat_description'],
                'unit_id' => $unit->id,
                'unit_price' => $item['unit_price'],
                'price_type_id' => $priceType->id,
            ]);
        }
    }
}
