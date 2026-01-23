<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pricelist;
use App\Models\Unit;
use App\Models\PriceType;

class PricelistCorrectivoBTSeeder4 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el tipo de precio "Mantenimiento Correctivos Baja Tensión"
        $priceType = PriceType::firstOrCreate(['name' => 'Mantenimiento Correctivos Baja Tensión']);

        // Datos de Mantenimiento Correctivos Baja Tensión - Parte 4
        $pricelists = [
            ['sat_line' => 'MCBT401', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X100A / 20KA/230VAC -  C120N - A9N18367', 'unit' => 'UNIDAD', 'unit_price' => 560.00],
            ['sat_line' => 'MCBT402', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X10A / 20KA/230VAC -  IC60N - A9F74310', 'unit' => 'UNIDAD', 'unit_price' => 193.00],
            ['sat_line' => 'MCBT403', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X10A / 5KA/230VAC -  IK60N  - A9K24310', 'unit' => 'UNIDAD', 'unit_price' => 118.00],
            ['sat_line' => 'MCBT404', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X125A / 20KA/230VAC -  C120N - A9N18369', 'unit' => 'UNIDAD', 'unit_price' => 680.00],
            ['sat_line' => 'MCBT405', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X16A / 20KA/230VAC -  IC60N - A9F74316', 'unit' => 'UNIDAD', 'unit_price' => 177.00],
            ['sat_line' => 'MCBT406', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X16A / 5KA/230VAC -  IK60N  - A9K24316', 'unit' => 'UNIDAD', 'unit_price' => 98.00],
            ['sat_line' => 'MCBT407', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X1A / 50KA/230VAC -  IC60N  - A9F74301', 'unit' => 'UNIDAD', 'unit_price' => 350.00],
            ['sat_line' => 'MCBT408', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X20A / 20KA/230VAC -  IC60N - A9F74320', 'unit' => 'UNIDAD', 'unit_price' => 163.41],
            ['sat_line' => 'MCBT409', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X20A / 5KA/230VAC -  IK60N  - A9K24320', 'unit' => 'UNIDAD', 'unit_price' => 108.53],
            ['sat_line' => 'MCBT410', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X25A / 20KA/230VAC -  IC60N - A9F74325', 'unit' => 'UNIDAD', 'unit_price' => 176.00],
            ['sat_line' => 'MCBT411', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X25A / 5KA/230VAC -  IK60N  - A9K24325', 'unit' => 'UNIDAD', 'unit_price' => 115.34],
            ['sat_line' => 'MCBT412', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X2A / 50KA/230VAC -  IC60N  - A9F74302', 'unit' => 'UNIDAD', 'unit_price' => 260.33],
            ['sat_line' => 'MCBT413', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X32A / 20KA/230VAC -  IC60N - A9F74332', 'unit' => 'UNIDAD', 'unit_price' => 209.72],
            ['sat_line' => 'MCBT414', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X32A / 5KA/230VAC -  IK60N  - A9K24332', 'unit' => 'UNIDAD', 'unit_price' => 136.00],
            ['sat_line' => 'MCBT415', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X40A / 20KA/230VAC -  IC60N - A9F74340', 'unit' => 'UNIDAD', 'unit_price' => 230.69],
            ['sat_line' => 'MCBT416', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X40A/ 5KA/230VAC -  IK60N  - A9K24340', 'unit' => 'UNIDAD', 'unit_price' => 131.08],
            ['sat_line' => 'MCBT417', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X4A / 50KA/230VAC -  IC60N  - A9F74304', 'unit' => 'UNIDAD', 'unit_price' => 251.00],
            ['sat_line' => 'MCBT418', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X50A / 20KA/230VAC -  IC60N - A9F74350', 'unit' => 'UNIDAD', 'unit_price' => 220.20],
            ['sat_line' => 'MCBT419', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X50A / 5KA/230VAC -  IK60N  - A9K24350', 'unit' => 'UNIDAD', 'unit_price' => 167.00],
            ['sat_line' => 'MCBT420', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X63A / 20KA/230VAC -  IC60N -A9F74363', 'unit' => 'UNIDAD', 'unit_price' => 262.15],
            ['sat_line' => 'MCBT421', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X63A / 5KA/230VAC -  IK60N  - A9K24363', 'unit' => 'UNIDAD', 'unit_price' => 188.74],
            ['sat_line' => 'MCBT422', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X6A / 20KA/230VAC -  IC60N  - A9F74306', 'unit' => 'UNIDAD', 'unit_price' => 207.60],
            ['sat_line' => 'MCBT423', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 3X80A / 20KA/230VAC -  C120N - A9N18365', 'unit' => 'UNIDAD', 'unit_price' => 512.78],
            ['sat_line' => 'MCBT424', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P NW, 1600A, TIPO H1, 3 POLOS, MICROLOGIC 5.0E, FIJO, MANUAL', 'unit' => 'UNIDAD', 'unit_price' => 14134.44],
            ['sat_line' => 'MCBT425', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE _112-160A- SCHENEIDER _ NSX160F', 'unit' => 'UNIDAD', 'unit_price' => 982.00],
            ['sat_line' => 'MCBT426', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE _140-200A- SCHENEIDER _ NSX250F', 'unit' => 'UNIDAD', 'unit_price' => 1194.62],
            ['sat_line' => 'MCBT427', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE _175-250A- SCHENEIDER _ NSX250F', 'unit' => 'UNIDAD', 'unit_price' => 1193.32],
            ['sat_line' => 'MCBT428', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE _35-50A- SCHENEIDER _ NSX100F', 'unit' => 'UNIDAD', 'unit_price' => 660.81],
            ['sat_line' => 'MCBT429', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE _44.1-63A- SCHENEIDER _ NSX100F', 'unit' => 'UNIDAD', 'unit_price' => 654.17],
            ['sat_line' => 'MCBT430', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE _56-80A- SCHENEIDER _ NSX100F', 'unit' => 'UNIDAD', 'unit_price' => 642.22],
            ['sat_line' => 'MCBT431', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE _70-100A- SCHENEIDER _ NSX100F', 'unit' => 'UNIDAD', 'unit_price' => 644.61],
            ['sat_line' => 'MCBT432', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE _87.5-125A- SCHENEIDER _ NSX160F', 'unit' => 'UNIDAD', 'unit_price' => 720.25],
            ['sat_line' => 'MCBT433', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE 160-400 - SCHNEIDER - NSX400N', 'unit' => 'UNIDAD', 'unit_price' => 2093.64],
            ['sat_line' => 'MCBT434', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE 252-630 - SCHNEIDER - NSX630N', 'unit' => 'UNIDAD', 'unit_price' => 4171.64],
            ['sat_line' => 'MCBT435', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE 320-800 - SCHNEIDER - NS800N', 'unit' => 'UNIDAD', 'unit_price' => 6190.34],
            ['sat_line' => 'MCBT436', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE 400-1000 - SCHNEIDER - NS1000N', 'unit' => 'UNIDAD', 'unit_price' => 8094.94],
            ['sat_line' => 'MCBT437', 'sat_description' => 'INTERRUPTOR TERMOMAGNETICO TIPO CAJA MOLDEADA  3P REGULABLE 500-1250 - SCHNEIDER - NS1250N', 'unit' => 'UNIDAD', 'unit_price' => 0.00],
            ['sat_line' => 'MCBT438', 'sat_description' => 'JACK PANDUIT _ RJ45 -CAT 5E', 'unit' => 'UNIDAD', 'unit_price' => 10.00],
            ['sat_line' => 'MCBT439', 'sat_description' => 'JUEGO DE BARRAS PARA TABLERO TRIFÁSICO_36 POLOS_CAPACIDAD DE 160A (INCLUYE AISLADORES)', 'unit' => 'UNIDAD', 'unit_price' => 181.40],
            ['sat_line' => 'MCBT440', 'sat_description' => 'JUEGO DE BARRAS PARA TABLERO TRIFÁSICO_48 POLOS_CAPACIDAD DE 160A (INCLUYE AISLADORES)', 'unit' => 'UNIDAD', 'unit_price' => 245.40],
            ['sat_line' => 'MCBT441', 'sat_description' => 'JUEGO DE BARRAS PARA TABLERO TRIFÁSICO_72 POLOS_CAPACIDAD DE 160A (INCLUYE AISLADORES)', 'unit' => 'UNIDAD', 'unit_price' => 284.40],
            ['sat_line' => 'MCBT442', 'sat_description' => 'LOGO 12/24RCE, LOGIC MODULE, DISPLAY PS/I/O: 12/24VDC/RELAY, 8 DI (4 AI)/4 DQ, SIEMENS', 'unit' => 'UNIDAD', 'unit_price' => 829.22],
            ['sat_line' => 'MCBT443', 'sat_description' => 'LUMINARIA LED ADOSADA/EMPOTRADA 12W, PANEL LED DAIRU, LUZ NEUTRA / FRÍA', 'unit' => 'UNIDAD', 'unit_price' => 44.00],
            ['sat_line' => 'MCBT444', 'sat_description' => 'LUMINARIA LED ADOSADA/EMPOTRADA 18W CON SENSOR DE MOVIMIENTO, LUZ CÁLIDA, PANEL LED DAIRU', 'unit' => 'UNIDAD', 'unit_price' => 44.00],
            ['sat_line' => 'MCBT445', 'sat_description' => 'LUMINARIA LED ADOSADA/EMPOTRADA 18W CON SENSOR DE MOVIMIENTO, LUZ FRÍA, PANEL LED DAIRU', 'unit' => 'UNIDAD', 'unit_price' => 44.00],
            ['sat_line' => 'MCBT446', 'sat_description' => 'LUMINARIA LED ADOSADA/EMPOTRADA 18W, PANEL LED DAIRU LUZ NEUTRA / FRÍA', 'unit' => 'UNIDAD', 'unit_price' => 29.57],
            ['sat_line' => 'MCBT447', 'sat_description' => 'LUMINARIA LED ADOSADA/EMPOTRADA 24W, PANEL LED DAIRU LUZ NEUTRA / FRÍA', 'unit' => 'UNIDAD', 'unit_price' => 44.00],
            ['sat_line' => 'MCBT448', 'sat_description' => 'LUMINARIA LED CIRCULAR 18W PHILLIPS, LUZ BLANCA/CÁLIDA', 'unit' => 'UNIDAD', 'unit_price' => 44.00],
            ['sat_line' => 'MCBT449', 'sat_description' => 'LUMINARIA LED CIRCULAR 32W PHILLIPS, LUZ BLANCA/CÁLIDA', 'unit' => 'UNIDAD', 'unit_price' => 44.00],
            ['sat_line' => 'MCBT450', 'sat_description' => 'LUMINARIA SPOT LIGHT  (2X18 W) LED', 'unit' => 'UNIDAD', 'unit_price' => 54.57],
            ['sat_line' => 'MCBT451', 'sat_description' => 'LUMINARIA SPOTLIGHT AIRIS LED DOWNLIGHT 8" 20W 2400LM 4000K', 'unit' => 'UNIDAD', 'unit_price' => 89.05],
            ['sat_line' => 'MCBT452', 'sat_description' => 'LUMINARIA TUBO LED 18W, 120CM LARGO, LUZ BLANCA, VOLTAJE AC 110 - 240V, VIDA ÚTIL 50000H, MARCA DIXON LIGHTING', 'unit' => 'UNIDAD', 'unit_price' => 29.59],
            ['sat_line' => 'MCBT453', 'sat_description' => 'LUMINARIA TUBO LED 36W, 120CM LARGO, LUZ BLANCA, VOLTAJE AC 110 - 240V, VIDA ÚTIL 50000H, MARCA DIXON LIGHTING', 'unit' => 'UNIDAD', 'unit_price' => 60.00],
            ['sat_line' => 'MCBT454', 'sat_description' => 'PATCH CORD CAT. 6 U/UTP 1 M PVC _ LEGRAND', 'unit' => 'UNIDAD', 'unit_price' => 23.06],
            ['sat_line' => 'MCBT455', 'sat_description' => 'PILOTO DE SEÑALIZACIÓN C/ LED (ROJO,AZUL, VERDE,ETC) 230 VAC', 'unit' => 'UNIDAD', 'unit_price' => 26.00],
            ['sat_line' => 'MCBT456', 'sat_description' => 'PLACA ACRÍLICA CON SEÑALIZACIÓN DE 15X21CMTS "PARADA DE EMERGENCIA"', 'unit' => 'UNIDAD', 'unit_price' => 80.00],
            ['sat_line' => 'MCBT457', 'sat_description' => 'PLACA PANDUIT DE 2 SALIDAS', 'unit' => 'UNIDAD', 'unit_price' => 13.00],
            ['sat_line' => 'MCBT458', 'sat_description' => 'PLC - RELE MODULAR PROGRAMABLE C/ RELOJ _ SCHENEIDER _ SR3B101FU _ 100 - 240 V - (6 ENTRADAS, 4 SALIDAS)', 'unit' => 'UNIDAD', 'unit_price' => 603.00],
            ['sat_line' => 'MCBT459', 'sat_description' => 'PM2200 ANALIZADOR DE ENERGÍA SCHNEIDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 1205.00],
            ['sat_line' => 'MCBT460', 'sat_description' => 'PM2230 ANALIZADOR DE ENERGÍA SCHENIEDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 1205.00],
            ['sat_line' => 'MCBT461', 'sat_description' => 'PRENSA ESTOPA HERMÉTICA - 1"', 'unit' => 'UNIDAD', 'unit_price' => 9.31],
            ['sat_line' => 'MCBT462', 'sat_description' => 'PRENSA ESTOPA HERMÉTICA - 1/2"', 'unit' => 'UNIDAD', 'unit_price' => 2.94],
            ['sat_line' => 'MCBT463', 'sat_description' => 'PRENSA ESTOPA HERMÉTICA - 3/4"', 'unit' => 'UNIDAD', 'unit_price' => 5.68],
            ['sat_line' => 'MCBT464', 'sat_description' => 'PULSADOR DE EMERGENCIA _ ROJO (NC) C/ ENCLAVAMIENTO _ SCHENEIDER', 'unit' => 'UNIDAD', 'unit_price' => 62.00],
            ['sat_line' => 'MCBT465', 'sat_description' => 'REGULADOR DE FACTOR DE POTENCIA REACTIVA 6 PASOS VPL - 6N - SCHNEIDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 1548.00],
            ['sat_line' => 'MCBT466', 'sat_description' => 'RELÉ DE FUGA A TIERRA VIGIREX RH99M CON REINICIO MANUAL, 0,03-30 A, 0-4,5 S, 240 V', 'unit' => 'UNIDAD', 'unit_price' => 767.00],
            ['sat_line' => 'MCBT467', 'sat_description' => 'RIEL DIN  SIMETRICO _ CAMSCO _ 35MM X 2M', 'unit' => 'M', 'unit_price' => 19.00],
            ['sat_line' => 'MCBT468', 'sat_description' => 'RIEL UNISTRUT ALTO DE 42 X 42 X 1000 MM DE F.G. P1000T RANURADO', 'unit' => 'M', 'unit_price' => 23.68],
            ['sat_line' => 'MCBT469', 'sat_description' => 'ROTULO PARA INTERRUPTORES _ ALUMINIO ANODIZADO (2X4CMTS)', 'unit' => 'UNIDAD', 'unit_price' => 18.00],
            ['sat_line' => 'MCBT470', 'sat_description' => 'ROTULO PARA LEYENDAS DE TABLERO _ ALUMINIO ANODIZADO (8X16CMTS)', 'unit' => 'UNIDAD', 'unit_price' => 24.00],
            ['sat_line' => 'MCBT471', 'sat_description' => 'ROTULO PARA TÍTULO DE TABLERO _  ALUMINIO ANODIZADO (3X10CMTS)', 'unit' => 'UNIDAD', 'unit_price' => 18.00],
            ['sat_line' => 'MCBT472', 'sat_description' => 'SELECTOR TIPO MANETA DE 2 PASOS SCHNEIDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 74.00],
            ['sat_line' => 'MCBT473', 'sat_description' => 'SENSOR DE PRESENCIA HUMANA TUYA WIFI/3 EN 1 ZIGBEE, RADAR DE ONDA MILIMÉTRICA DE 24G, DETECCIÓN PIR, DETECTOR DE DISTANCIA DE LUMINOSIDAD', 'unit' => 'UNIDAD', 'unit_price' => 0.00],
            ['sat_line' => 'MCBT474', 'sat_description' => 'SEPARADOR PARA CANALETA DE 110X50 10099R EFAPEL', 'unit' => 'UNIDAD', 'unit_price' => 4.19],
            ['sat_line' => 'MCBT475', 'sat_description' => 'TABLERO DE PVC DE 10 POLOS', 'unit' => 'UNIDAD', 'unit_price' => 34.68],
            ['sat_line' => 'MCBT476', 'sat_description' => 'TABLERO DISTRIBUCIÓN TRIFÁSICO ELÉCTRICO 14 POLOS PARA ITM\'S RIEL DIN, INCLUYE BARRA INTERNA, MANDIL ABISAGRADO, IP54, PLANCHA GALVANIZADA 2MM, PINTURA RAL.', 'unit' => 'UNIDAD', 'unit_price' => 370.00],
            // User provided updates start here
            ['sat_line' => 'C', 'sat_description' => 'TABLERO METALICO PARA ADOSAR DE 12 POLOS PLANCHA DE 1.9MM ESPESOR', 'unit' => 'UNIDAD', 'unit_price' => 236.44],
            ['sat_line' => 'MCBT479', 'sat_description' => 'TABLERO METALICO PARA ADOSAR DE 20 POLOS PLANCHA DE 1.9MM ESPESOR', 'unit' => 'UNIDAD', 'unit_price' => 261.86],
            ['sat_line' => 'MCBT480', 'sat_description' => 'TABLERO METALICO PARA ADOSAR DE 30 POLOS PLANCHA DE 1.9MM ESPESOR', 'unit' => 'UNIDAD', 'unit_price' => 372.00],
            ['sat_line' => 'MCBT481', 'sat_description' => 'TABLERO METALICO PARA ADOSAR DE 40 POLOS PLANCHA DE 1.9MM ESPESOR', 'unit' => 'UNIDAD', 'unit_price' => 406.00],
            ['sat_line' => 'MCBT482', 'sat_description' => 'TAPA CIEGA (RESERVA DE INTERRUPTORES EN TABLEROS)', 'unit' => 'UNIDAD', 'unit_price' => 1.04],
            ['sat_line' => 'MCBT483', 'sat_description' => 'TAPA CIEGA DE METAL _ 10" X 10"', 'unit' => 'UNIDAD', 'unit_price' => 13.99],
            ['sat_line' => 'MCBT484', 'sat_description' => 'TAPA CIEGA DE METAL _ 8" X 8"', 'unit' => 'UNIDAD', 'unit_price' => 13.63],
            ['sat_line' => 'MCBT485', 'sat_description' => 'TAPA CIEGA DE PVC RECTANGULAR 4X2"', 'unit' => 'UNIDAD', 'unit_price' => 0.98],
            ['sat_line' => 'MCBT486', 'sat_description' => 'TAPA FINAL DE 110 X 50 MM 10095RBR EFAPEL', 'unit' => 'UNIDAD', 'unit_price' => 7.00],
            ['sat_line' => 'MCBT487', 'sat_description' => 'TAPA FINAL DE 20 X 12.5 MM EFAPHEL', 'unit' => 'UNIDAD', 'unit_price' => 7.00],
            ['sat_line' => 'MCBT488', 'sat_description' => 'TAPA FINAL DE 50 X 20 MM LEGRAND DLP-S 638165', 'unit' => 'UNIDAD', 'unit_price' => 1.47],
            ['sat_line' => 'MCBT489', 'sat_description' => 'TEE DE DERIVACION DE CANALETA DE 110 X 50 MM 10091RBR EFAPEL', 'unit' => 'UNIDAD', 'unit_price' => 11.00],
            ['sat_line' => 'MCBT490', 'sat_description' => 'TEE DE DERIVACION DE CANALETA DE 50 X 20 MM LEGRAND - DLP-S 638164', 'unit' => 'UNIDAD', 'unit_price' => 11.00],
            ['sat_line' => 'MCBT491', 'sat_description' => 'TERMINAL PARA CABLE DE 1.5 MM2', 'unit' => 'UNIDAD', 'unit_price' => 0.12],
            ['sat_line' => 'MCBT492', 'sat_description' => 'TERMINAL PARA CABLE DE 2.5 MM2', 'unit' => 'UNIDAD', 'unit_price' => 0.12],
            ['sat_line' => 'MCBT493', 'sat_description' => 'TERMINAL PARA CABLE DE 4 MM2', 'unit' => 'UNIDAD', 'unit_price' => 0.17],
            ['sat_line' => 'MCBT494', 'sat_description' => 'TERMINAL PARA CABLE DE 6 MM2', 'unit' => 'UNIDAD', 'unit_price' => 0.19],
            ['sat_line' => 'MCBT495', 'sat_description' => 'TERMINALES P/ CABLES DE ENERGIA 10 MM2 HUECO DE 1/2 _ TALMA', 'unit' => 'UNIDAD', 'unit_price' => 1.76],
            ['sat_line' => 'MCBT496', 'sat_description' => 'TERMINALES P/ CABLES DE ENERGIA 16 MM2 HUECO DE 3/8  _ TALMA', 'unit' => 'UNIDAD', 'unit_price' => 2.45],
            ['sat_line' => 'MCBT497', 'sat_description' => 'TERMINALES P/ CABLES DE ENERGIA 25 MM2 HUECO DE 1/2 _ TALMA', 'unit' => 'UNIDAD', 'unit_price' => 4.97],
            ['sat_line' => 'MCBT498', 'sat_description' => 'TERMINALES P/ CABLES DE ENERGIA 35 MM2 HUECO DE 1/2  _ TALMA', 'unit' => 'UNIDAD', 'unit_price' => 5.05],
            ['sat_line' => 'MCBT499', 'sat_description' => 'TERMINALES P/ CABLES DE ENERGIA 50 MM2 HUECO 1/2 _ TALMA', 'unit' => 'UNIDAD', 'unit_price' => 7.50],
            ['sat_line' => 'MCBT500', 'sat_description' => 'TIERRA DE CULTIVO (CHACRA)', 'unit' => 'M3', 'unit_price' => 150.00],
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
