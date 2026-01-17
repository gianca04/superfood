<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pricelist;
use App\Models\Unit;
use App\Models\PriceType;

class PricelistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el tipo de precio "Mantenimiento Preventivo Baja Tensión"
        $priceType = PriceType::firstOrCreate(['name' => 'Mantenimiento Preventivo Baja Tensión']);

        // Datos de Mantenimiento Preventivo Baja Tensión
        $pricelists = [
            ['sat_line' => 'MPBT01', 'sat_description' => 'MP TABLERO ADOSADO O EMPOTRADO', 'unit' => 'UNIDAD', 'unit_price' => 81.86],
            ['sat_line' => 'MPBT02', 'sat_description' => 'MP TABLERO AUTOSOPORTADO', 'unit' => 'UNIDAD', 'unit_price' => 93.00],
            ['sat_line' => 'MPBT03', 'sat_description' => 'MP POZO A TIERRA T_CONVENCIONAL BT', 'unit' => 'UNIDAD', 'unit_price' => 219.51],
            ['sat_line' => 'MPBT04', 'sat_description' => 'MP POZO A TIERRA T_MALLA BT', 'unit' => 'UNIDAD', 'unit_price' => 219.51],
            ['sat_line' => 'MPBT05', 'sat_description' => 'MP TABLERO BCO CONDENSADORES', 'unit' => 'UNIDAD', 'unit_price' => 221.89],
            ['sat_line' => 'MPBT06', 'sat_description' => 'MP GENERADOR ESTATICO REACTIVOS', 'unit' => 'UNIDAD', 'unit_price' => 234.04],
            ['sat_line' => 'MPBT07', 'sat_description' => 'MP FILTRO DE ARMONICOS', 'unit' => 'UNIDAD', 'unit_price' => 240.89],
            ['sat_line' => 'MPBT08', 'sat_description' => 'SERV. MP VARIADORES', 'unit' => 'UNIDAD', 'unit_price' => 214.89],
            ['sat_line' => 'MPBT09', 'sat_description' => 'PDR PREVENCIONISTA BT (HONORARIOS)', 'unit' => 'POR VISITA / SEDE', 'unit_price' => 165.76],
            ['sat_line' => 'MPBT10', 'sat_description' => 'VIATICO DIA ADICIONAL RUTA  (HOSPEDAJE Y ALIMENTACIÓN)', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 117.00],
            ['sat_line' => 'MPBT11', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  0 A 10 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 26.52],
            ['sat_line' => 'MPBT12', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  11 A 20 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 26.52],
            ['sat_line' => 'MPBT13', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  21 A 30 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 33.75],
            ['sat_line' => 'MPBT14', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  31 A 40 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 33.75],
            ['sat_line' => 'MPBT15', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  41 A 50 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 41.25],
            ['sat_line' => 'MPBT16', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  51 A 60 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 41.25],
            ['sat_line' => 'MPBT17', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  61 A 70 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 41.25],
            ['sat_line' => 'MPBT18', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  71 A 80 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 41.25],
            ['sat_line' => 'MPBT19', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  81 A 90 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 41.25],
            ['sat_line' => 'MPBT20', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  91 A 100 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 41.25],
            ['sat_line' => 'MPBT21', 'sat_description' => 'GESTION INVENTARIO ELEC TIENDA DE  MÁS DE 100 EQUIPOS', 'unit' => 'TIENDA', 'unit_price' => 41.25],
            ['sat_line' => 'MPBT22', 'sat_description' => 'VIATICO LIMA ( CAÑETE, MALA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT23', 'sat_description' => 'VIATICO SUR CHICO ( ASIA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT24', 'sat_description' => 'VIATICO SUR ( AREQUIPA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT25', 'sat_description' => 'VIATICO SUR ( CUSCO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT26', 'sat_description' => 'VIATICO SUR ( ILO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT27', 'sat_description' => 'VIATICO SUR ( JULIACA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT28', 'sat_description' => 'VIATICO SUR ( MOQUEGUA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT29', 'sat_description' => 'VIATICO SUR ( PUNO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT30', 'sat_description' => 'VIATICO SUR ( TACNA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT31', 'sat_description' => 'VIATICO SUR CHICO ( CHINCHA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT32', 'sat_description' => 'VIATICO SUR CHICO ( ICA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT33', 'sat_description' => 'VIATICO SUR CHICO ( PISCO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA /  DIA / SEDE', 'unit_price' => null],
            ['sat_line' => 'MPBT34', 'sat_description' => 'PDR PREVENCIONISTA BT (HONORARIOS)', 'unit' => 'POR VISITA / SEDE', 'unit_price' => 165.76],
            ['sat_line' => 'MPBT35', 'sat_description' => 'VIATICO CENTRO ( HUANCAYO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => null],
            ['sat_line' => 'MPBT36', 'sat_description' => 'VIATICO NORTE ( CAJAMARCA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 267.28],
            ['sat_line' => 'MPBT37', 'sat_description' => 'VIATICO NORTE ( TRUJILLO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 190.00],
            ['sat_line' => 'MPBT38', 'sat_description' => 'VIATICO NORTE ( CHICLAYO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 177.83],
            ['sat_line' => 'MPBT39', 'sat_description' => 'VIATICO NORTE ( CHIMBOTE) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 202.00],
            ['sat_line' => 'MPBT40', 'sat_description' => 'VIATICO NORTE ( PIURA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 29.00],
            ['sat_line' => 'MPBT41', 'sat_description' => 'VIATICO NORTE ( JAÉN) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 206.37],
            ['sat_line' => 'MPBT42', 'sat_description' => 'VIATICO NORTE ( PAITA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 34.00],
            ['sat_line' => 'MPBT43', 'sat_description' => 'VIATICO NORTE ( SULLANA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 34.00],
            ['sat_line' => 'MPBT44', 'sat_description' => 'VIATICO NORTE ( TALARA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 48.00],
            ['sat_line' => 'MPBT45', 'sat_description' => 'VIATICO NORTE ( TUMBES) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => 140.00],
            ['sat_line' => 'MPBT46', 'sat_description' => 'VIATICO NORTE CHICO ( BARRANCA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => null],
            ['sat_line' => 'MPBT47', 'sat_description' => 'VIATICO NORTE CHICO ( HUACHO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => null],
            ['sat_line' => 'MPBT48', 'sat_description' => 'VIATICO NORTE CHICO ( HUARAL) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => null],
            ['sat_line' => 'MPBT49', 'sat_description' => 'VIATICO ORIENTE ( HUÁNUCO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => null],
            ['sat_line' => 'MPBT50', 'sat_description' => 'VIATICO ORIENTE ( PUCALLPA) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => null],
            ['sat_line' => 'MPBT51', 'sat_description' => 'VIATICO ORIENTE ( TARAPOTO) - ALIMENTACIÓN, HOSPEDAJE Y PASAJE I/V', 'unit' => 'POR RUTA X SEDE X DIA', 'unit_price' => null],
            ['sat_line' => 'MPBT52', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN EXTERIORES MAX 15M  (POSTES, PAREDES, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 1 A 20 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT53', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN EXTERIORES MAX 15M  (POSTES, PAREDES, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 21 A 50 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT54', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN EXTERIORES MAX 15M  (POSTES, PAREDES, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 51 A 70 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT55', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN EXTERIORES MAX 15M  (POSTES, PAREDES, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 71  A MÁS UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT56', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN SALA DE VENTAS MAX. 5M (INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 1 A 20 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT57', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN SALA DE VENTAS MAX. 5M (INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 21 A 50 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT58', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN SALA DE VENTAS MAX. 5M (INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 51 A 70 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT59', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN SALA DE VENTAS MAX. 5M (INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 71  A MÁS UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT60', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN TRANSTIENDA MAX. 3.5M (OFICINAS, ALMACENES, SSHH, PLAYA ESTACIONAMIENTO, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 1 A 20 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT61', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN TRANSTIENDA MAX. 3.5M (OFICINAS, ALMACENES, SSHH, PLAYA ESTACIONAMIENTO, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 21 A 50 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT62', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN TRANSTIENDA MAX. 3.5M (OFICINAS, ALMACENES, SSHH, PLAYA ESTACIONAMIENTO, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 51 A 70 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT63', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN TRANSTIENDA MAX. 3.5M (OFICINAS, ALMACENES, SSHH, PLAYA ESTACIONAMIENTO, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 71  A MÁS UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT64', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN TRANSTIENDA MAX. 5M (OFICINAS, ALMACENES, SSHH, PLAYA ESTACIONAMIENTO, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 1 A 20 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT65', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN TRANSTIENDA MAX. 5M (OFICINAS, ALMACENES, SSHH, PLAYA ESTACIONAMIENTO, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 21 A 50 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT66', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN TRANSTIENDA MAX. 5M (OFICINAS, ALMACENES, SSHH, PLAYA ESTACIONAMIENTO, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 51 A 70 UNIDADES', 'unit_price' => null],
            ['sat_line' => 'MPBT67', 'sat_description' => 'LIMPIEZA DE PANTALLA Y/O DIFUSOR DE LUMINARIA EN TRANSTIENDA MAX. 5M (OFICINAS, ALMACENES, SSHH, PLAYA ESTACIONAMIENTO, ETC, INCLUYE TODAS LAS FACILIDADES QUE SE REQUIERAN PARA EJECUTAR EL TRABAJO EN ALTURA).', 'unit' => 'DE 71  A MÁS UNIDADES', 'unit_price' => null],
        ];

        foreach ($pricelists as $item) {
            // Buscar la unidad por symbol, si no existe, crearla
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
