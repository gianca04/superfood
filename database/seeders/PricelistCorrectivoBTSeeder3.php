<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pricelist;
use App\Models\Unit;
use App\Models\PriceType;

class PricelistCorrectivoBTSeeder3 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el tipo de precio "Mantenimiento Correctivos Baja Tensión"
        $priceType = PriceType::firstOrCreate(['name' => 'Mantenimiento Correctivos Baja Tensión']);

        // Datos de Mantenimiento Correctivos Baja Tensión - Parte 3
        $pricelists = [
            ['sat_line' => 'MCBT301', 'sat_description' => 'DETECTOR DE PRESENCIA HUMANA INTELIGENTE PIR OPALUX 16A', 'unit' => 'UNIDAD', 'unit_price' => 73.00],
            ['sat_line' => 'MCBT302', 'sat_description' => 'DOSIS QUÍMICA PARA PUESTA A TIERRA THORGEL (5 KGS)', 'unit' => 'UNIDAD', 'unit_price' => 115.40],
            ['sat_line' => 'MCBT303', 'sat_description' => 'ENCHUFE 16 A (2P+T) - 250 V (AZUL) 6H _ MENNEKES _ 278 _ IP67', 'unit' => 'UNIDAD', 'unit_price' => 25.00],
            ['sat_line' => 'MCBT304', 'sat_description' => 'ENCHUFE 16 A (3P+T) - 250 V (AZUL) 6H _ MENNEKES _ 278 _ IP67', 'unit' => 'UNIDAD', 'unit_price' => 36.00],
            ['sat_line' => 'MCBT305', 'sat_description' => 'ENCHUFE 16 A (3P+T+N) - 250 V (AZUL) 6H _ MENNEKES _ 278 _ IP67', 'unit' => 'UNIDAD', 'unit_price' => 32.39],
            ['sat_line' => 'MCBT306', 'sat_description' => 'ENCHUFE 2X15 A (AMARILLO) _ LEVINTON', 'unit' => 'UNIDAD', 'unit_price' => 10.90],
            ['sat_line' => 'MCBT307', 'sat_description' => 'ENCHUFE 32 A (2P+T) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP44', 'unit' => 'UNIDAD', 'unit_price' => 28.20],
            ['sat_line' => 'MCBT308', 'sat_description' => 'ENCHUFE 32 A (3P+T) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP44', 'unit' => 'UNIDAD', 'unit_price' => 39.40],
            ['sat_line' => 'MCBT309', 'sat_description' => 'ENCHUFE 32 A (3P+T+N) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP44', 'unit' => 'UNIDAD', 'unit_price' => 39.00],
            ['sat_line' => 'MCBT310', 'sat_description' => 'ENCHUFE 63 A (2P+T) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP45', 'unit' => 'UNIDAD', 'unit_price' => 166.30],
            ['sat_line' => 'MCBT311', 'sat_description' => 'ENCHUFE 63 A (3P+T) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP45', 'unit' => 'UNIDAD', 'unit_price' => 176.00],
            ['sat_line' => 'MCBT312', 'sat_description' => 'ENCHUFE 63 A (3P+T+N) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP45', 'unit' => 'UNIDAD', 'unit_price' => 167.70],
            ['sat_line' => 'MCBT313', 'sat_description' => 'EQUIPO HERMETICO  LINEAL 2X18WATTS LEDVANCE', 'unit' => 'UNIDAD', 'unit_price' => 72.71],
            ['sat_line' => 'MCBT314', 'sat_description' => 'EQUIPO HERMETICO  LINEAL 2X36WATTS LEDVANCE', 'unit' => 'UNIDAD', 'unit_price' => 79.00],
            ['sat_line' => 'MCBT315', 'sat_description' => 'ESPIRAL PORTACABLE KS 10 (10 MTS) X (11.4-7.5MM)MAX. 10.CCKS', 'unit' => 'ML', 'unit_price' => 3.00],
            ['sat_line' => 'MCBT316', 'sat_description' => 'ESTABILIZADOR (MONOFÁSICO) _ ELISE IEDA PODER LCR-30 _ SOLIDO _ 3.0 KVA, 220V, 6 CONECTORES DE SALIDA', 'unit' => 'UNIDAD', 'unit_price' => 757.00],
            ['sat_line' => 'MCBT317', 'sat_description' => 'ESTABILIZADOR FERRORESONANTE 220V - 10KVA', 'unit' => 'UNIDAD', 'unit_price' => 0.00],
            ['sat_line' => 'MCBT318', 'sat_description' => 'ESTABILIZADOR FERRORESONANTE 220V - 20KVA', 'unit' => 'UNIDAD', 'unit_price' => 0.00],
            ['sat_line' => 'MCBT319', 'sat_description' => 'ESTABILIZADOR  FERRORESONANTE  (MONOFÁSICO) _ 10 KVA', 'unit' => 'UNIDAD', 'unit_price' => 0.00],
            ['sat_line' => 'MCBT320', 'sat_description' => 'ESTABILIZADOR  FERRORESONANTE  (MONOFÁSICO) _ 6.0 KVA', 'unit' => 'UNIDAD', 'unit_price' => 0.00],
            ['sat_line' => 'MCBT321', 'sat_description' => 'FUSIBLE DE VIDRIO (1 A)', 'unit' => 'UNIDAD', 'unit_price' => 1.00],
            ['sat_line' => 'MCBT322', 'sat_description' => 'FUSIBLE DE VIDRIO (2 A)', 'unit' => 'UNIDAD', 'unit_price' => 0.97],
            ['sat_line' => 'MCBT323', 'sat_description' => 'FUSIBLE DE VIDRIO (3 A)', 'unit' => 'UNIDAD', 'unit_price' => 1.80],
            ['sat_line' => 'MCBT324', 'sat_description' => 'FUSIBLE DE VIDRIO (5 A)', 'unit' => 'UNIDAD', 'unit_price' => 1.52],
            ['sat_line' => 'MCBT325', 'sat_description' => 'INTERRUPTOR  TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER1X20A  / 5KA/230VAC - A9K24120 IK60N', 'unit' => 'UNIDAD', 'unit_price' => 28.00],
            ['sat_line' => 'MCBT326', 'sat_description' => 'INTERRUPTOR 3 VÍAS (MAGIC) _ TICINO _ 5003 _ 16 A - 250 V', 'unit' => 'UNIDAD', 'unit_price' => 23.00],
            ['sat_line' => 'MCBT327', 'sat_description' => 'INTERRUPTOR AUTOMÁTICO COMPACT 3P NSX630N MICROLOGIC 1.3 M 500 A', 'unit' => 'UNIDAD', 'unit_price' => 3013.98],
            ['sat_line' => 'MCBT328', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X25 A-  IID-A -A9R51225', 'unit' => 'UNIDAD', 'unit_price' => 221.72],
            ['sat_line' => 'MCBT329', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X25 A-  IID-AC -A9R71225', 'unit' => 'UNIDAD', 'unit_price' => 199.35],
            ['sat_line' => 'MCBT330', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X25 A-  IIDSI-A9R91225', 'unit' => 'UNIDAD', 'unit_price' => 249.06],
            ['sat_line' => 'MCBT331', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X25 A- IIDK - A9R50225', 'unit' => 'UNIDAD', 'unit_price' => 178.62],
            ['sat_line' => 'MCBT332', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X40 A-  IID-A-A9R51240', 'unit' => 'UNIDAD', 'unit_price' => 250.00],
            ['sat_line' => 'MCBT333', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X40 A-  IID-AC -A9R71240', 'unit' => 'UNIDAD', 'unit_price' => 258.08],
            ['sat_line' => 'MCBT334', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X40 A-  IIDK - A9R50240', 'unit' => 'UNIDAD', 'unit_price' => 230.69],
            ['sat_line' => 'MCBT335', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X40 A-  IIDSI-A9R91240-SUPERINMUNIZADO', 'unit' => 'UNIDAD', 'unit_price' => 326.83],
            ['sat_line' => 'MCBT336', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X63 A-  IID-A -A9R51263', 'unit' => 'UNIDAD', 'unit_price' => 470.25],
            ['sat_line' => 'MCBT337', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X63 A-  IID-AC -A9R71263', 'unit' => 'UNIDAD', 'unit_price' => 329.08],
            ['sat_line' => 'MCBT338', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 2X63 A-  IIDSI-A9R91263-SUPERINMUNIZADO', 'unit' => 'UNIDAD', 'unit_price' => 429.32],
            ['sat_line' => 'MCBT339', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 4X25 A-  IID-A -A9R51425', 'unit' => 'UNIDAD', 'unit_price' => 509.21],
            ['sat_line' => 'MCBT340', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 4X25 A-  IID-AC -A9R71425', 'unit' => 'UNIDAD', 'unit_price' => 350.00],
            ['sat_line' => 'MCBT341', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 4X25 A-  IIDSI-A9R91425-SUPERINMUNIZADO', 'unit' => 'UNIDAD', 'unit_price' => 393.18],
            ['sat_line' => 'MCBT342', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 4X40 A-  IID-A -A9R51440', 'unit' => 'UNIDAD', 'unit_price' => 536.09],
            ['sat_line' => 'MCBT343', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 4X40 A-  IID-AC -A9R71440', 'unit' => 'UNIDAD', 'unit_price' => 383.18],
            ['sat_line' => 'MCBT344', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 4X40 A-  IIDSI-A9R91440-SUPERINMUNIZADO', 'unit' => 'UNIDAD', 'unit_price' => 428.26],
            ['sat_line' => 'MCBT345', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 4X63 A-  IID-A -A9R51463', 'unit' => 'UNIDAD', 'unit_price' => 741.50],
            ['sat_line' => 'MCBT346', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 4X63 A-  IID-AC -A9R71463', 'unit' => 'UNIDAD', 'unit_price' => 412.72],
            ['sat_line' => 'MCBT347', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 30 MA - 4X63 A-  IIDSI-A9R91463-SUPERINMUNIZADO', 'unit' => 'UNIDAD', 'unit_price' => 664.79],
            ['sat_line' => 'MCBT348', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 300 MA - 2X25 A-  IID-AC -A9R74225', 'unit' => 'UNIDAD', 'unit_price' => 173.01],
            ['sat_line' => 'MCBT349', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 300 MA - 2X40 A-  IID-AC -A9R74240', 'unit' => 'UNIDAD', 'unit_price' => 230.55],
            ['sat_line' => 'MCBT350', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 300 MA - 2X63 A-  IID-AC -A9R74263', 'unit' => 'UNIDAD', 'unit_price' => 346.03],
            ['sat_line' => 'MCBT351', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 300 MA - 4X40 A-  IID-AC -A9R74440', 'unit' => 'UNIDAD', 'unit_price' => 253.00],
            ['sat_line' => 'MCBT352', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 300 MA - 4X63 A-  IID-AC -A9R74463', 'unit' => 'UNIDAD', 'unit_price' => 471.87],
            ['sat_line' => 'MCBT353', 'sat_description' => 'INTERRUPTOR DIFERENCIAL (PARA RIEL DIN) _ SCHENEIDER _ 300MA - 4X25 A-  IID-AC -A9R74425', 'unit' => 'UNIDAD', 'unit_price' => 262.15],
            ['sat_line' => 'MCBT354', 'sat_description' => 'INTERRUPTOR HORARIO ANALOGICO _ SCHENEIDER _ 16 A', 'unit' => 'UNIDAD', 'unit_price' => 312.96],
            ['sat_line' => 'MCBT355', 'sat_description' => 'INTERRUPTOR HORARIO DIGITAL _ SCHENEIDER _ 16 A', 'unit' => 'UNIDAD', 'unit_price' => 631.46],
            ['sat_line' => 'MCBT356', 'sat_description' => 'INTERRUPTOR HORARIO TUYA SMART SINOTIMER TM 607, CONTROL REMOTO DE ENCENDIDO MONOFASICO 220V 80A', 'unit' => 'UNIDAD', 'unit_price' => 212.00],
            ['sat_line' => 'MCBT357', 'sat_description' => 'INTERRUPTOR HORARIO TUYA SMART SINOTIMER TM 608, CONTROL REMOTO DE ENCENDIDO MONOFASICO 220V 16A', 'unit' => 'UNIDAD', 'unit_price' => 240.50],
            ['sat_line' => 'MCBT358', 'sat_description' => 'INTERRUPTOR HORARIO TUYA SMART SINOTIMER TM609, CONTROL REMOTO DE ENCENDIDO MONOFASICO 220V 16A', 'unit' => 'UNIDAD', 'unit_price' => 251.00],
            ['sat_line' => 'MCBT359', 'sat_description' => 'INTERRUPTOR SIMPLE (MAGIC) _ TICINO _ 5001 _ 16 A - 250 V', 'unit' => 'UNIDAD', 'unit_price' => 22.43],
            ['sat_line' => 'MCBT360', 'sat_description' => 'INTERRUPTOR SIMPLE (MODULOS LIGHT) _ TICINO _ N4001 - 1P _ 16 A - 250 V', 'unit' => 'UNIDAD', 'unit_price' => 36.12],
            ['sat_line' => 'MCBT361', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER  1X16A  / 5KA/230VAC - A9K24116 IK60N', 'unit' => 'UNIDAD', 'unit_price' => 28.31],
            ['sat_line' => 'MCBT362', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER _ IC60N _ 1X10 A - 20/10/6 KA 240/380/440 V', 'unit' => 'UNIDAD', 'unit_price' => 57.00],
            ['sat_line' => 'MCBT363', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X100A / 10KA/230VAC -  IC120N  - A9N18358', 'unit' => 'UNIDAD', 'unit_price' => 270.00],
            ['sat_line' => 'MCBT364', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X10A / 10KA/230VAC -  IC60N  - A9F74110', 'unit' => 'UNIDAD', 'unit_price' => 57.02],
            ['sat_line' => 'MCBT365', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X10A / 5KA/230VAC - A9K24110 IK60N', 'unit' => 'UNIDAD', 'unit_price' => 29.11],
            ['sat_line' => 'MCBT366', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X16A / 10KA/230VAC -  IC60N  - A9F74116', 'unit' => 'UNIDAD', 'unit_price' => 67.11],
            ['sat_line' => 'MCBT367', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X1A / 50KA/230VAC -  IC60N  - A9F74101', 'unit' => 'UNIDAD', 'unit_price' => 69.87],
            ['sat_line' => 'MCBT368', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X20A / 10KA/230VAC -  IC60N  - A9F74120', 'unit' => 'UNIDAD', 'unit_price' => 62.00],
            ['sat_line' => 'MCBT369', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X25A  / 5KA/230VAC - A9K24125 IK60N', 'unit' => 'UNIDAD', 'unit_price' => 29.36],
            ['sat_line' => 'MCBT370', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X25A / 10KA/230VAC -  IC60N  - A9F74125', 'unit' => 'UNIDAD', 'unit_price' => 68.00],
            ['sat_line' => 'MCBT371', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X2A / 50KA/230VAC -  IC60N  - A9F74102', 'unit' => 'UNIDAD', 'unit_price' => 69.87],
            ['sat_line' => 'MCBT372', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X32A / 10KA/230VAC -  IC60N  - A9F74132', 'unit' => 'UNIDAD', 'unit_price' => 68.00],
            ['sat_line' => 'MCBT373', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X40A / 10KA/230VAC -  IC60N  - A9F74140', 'unit' => 'UNIDAD', 'unit_price' => 81.00],
            ['sat_line' => 'MCBT374', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X4A / 50KA/230VAC -  IC60N  - A9F74104', 'unit' => 'UNIDAD', 'unit_price' => 71.00],
            ['sat_line' => 'MCBT375', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X50A / 10KA/230VAC -  IC60N  - A9F74150', 'unit' => 'UNIDAD', 'unit_price' => 93.32],
            ['sat_line' => 'MCBT376', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X63A / 10KA/230VAC -  IC60N  - A9F74163', 'unit' => 'UNIDAD', 'unit_price' => 89.13],
            ['sat_line' => 'MCBT377', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 1X6A / 10KA/230VAC -  IC60N  - A9F74106', 'unit' => 'UNIDAD', 'unit_price' => 68.00],
            ['sat_line' => 'MCBT378', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X100A / 20KA/230VAC -  C120N  - A9N18362', 'unit' => 'UNIDAD', 'unit_price' => 419.44],
            ['sat_line' => 'MCBT379', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X10A / 20KA/230VAC -  IC60N  - A9F74210', 'unit' => 'UNIDAD', 'unit_price' => 103.00],
            ['sat_line' => 'MCBT380', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X10A / 5KA/230VAC -  IK60N  - A9K24210', 'unit' => 'UNIDAD', 'unit_price' => 57.67],
            ['sat_line' => 'MCBT381', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X125A / 20KA/230VAC -  C120N  - A9N18363', 'unit' => 'UNIDAD', 'unit_price' => 416.90],
            ['sat_line' => 'MCBT382', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X16A / 20KA/230VAC -  IC60N  - A9F74216', 'unit' => 'UNIDAD', 'unit_price' => 94.00],
            ['sat_line' => 'MCBT383', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X16A / 5KA/230VAC -  IK60N  - A9K24216', 'unit' => 'UNIDAD', 'unit_price' => 52.43],
            ['sat_line' => 'MCBT384', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X1A / 50KA/230VAC -  IC60N  - A9F74201', 'unit' => 'UNIDAD', 'unit_price' => 161.48],
            ['sat_line' => 'MCBT385', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X20A / 20KA/230VAC -  IC60N  - A9F74220', 'unit' => 'UNIDAD', 'unit_price' => 94.00],
            ['sat_line' => 'MCBT386', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X20A / 5KA/230VAC -  IK60N  - A9K24220', 'unit' => 'UNIDAD', 'unit_price' => 50.00],
            ['sat_line' => 'MCBT387', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X25A / 20KA/230VAC -  IC60N  - A9F74225', 'unit' => 'UNIDAD', 'unit_price' => 97.00],
            ['sat_line' => 'MCBT388', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X25A / 5KA/230VAC -  IK60N  - A9K24225', 'unit' => 'UNIDAD', 'unit_price' => 47.18],
            ['sat_line' => 'MCBT389', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X2A / 50KA/230VAC -  IC60N  - A9F74202', 'unit' => 'UNIDAD', 'unit_price' => 141.00],
            ['sat_line' => 'MCBT390', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X32A / 20KA/230VAC -  IC60N  - A9F74232', 'unit' => 'UNIDAD', 'unit_price' => 89.11],
            ['sat_line' => 'MCBT391', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X32A/ 5KA/230VAC -  IK60N  - A9K24232', 'unit' => 'UNIDAD', 'unit_price' => 62.83],
            ['sat_line' => 'MCBT392', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X40A / 20KA/230VAC -  IC60N  - A9F74240', 'unit' => 'UNIDAD', 'unit_price' => 147.00],
            ['sat_line' => 'MCBT393', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X40A / 5KA/230VAC -  IK60N  - A9K24240', 'unit' => 'UNIDAD', 'unit_price' => 68.00],
            ['sat_line' => 'MCBT394', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X4A / 50KA/230VAC -  IC60N  - A9F74204', 'unit' => 'UNIDAD', 'unit_price' => 141.00],
            ['sat_line' => 'MCBT395', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X50A / 20KA/230VAC -  IC60N  - A9F74250', 'unit' => 'UNIDAD', 'unit_price' => 147.63],
            ['sat_line' => 'MCBT396', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X50A / 5KA/230VAC -  IK60N  - A9K24250', 'unit' => 'UNIDAD', 'unit_price' => 73.80],
            ['sat_line' => 'MCBT397', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X63A / 20KA/230VAC -  IC60N  - A9F74263', 'unit' => 'UNIDAD', 'unit_price' => 146.80],
            ['sat_line' => 'MCBT398', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X63A / 5KA/230VAC -  IK60N  - A9K24263', 'unit' => 'UNIDAD', 'unit_price' => 94.37],
            ['sat_line' => 'MCBT399', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X6A / 20KA/230VAC -  IC60N  - A9F74206', 'unit' => 'UNIDAD', 'unit_price' => 129.00],
            ['sat_line' => 'MCBT400', 'sat_description' => 'INTERRUPTOR TERMOMAGNÉTICO (PARA RIEL DIN) _ SCHENEIDER 2X80A / 20KA/230VAC -  C120N  - A9N18361', 'unit' => 'UNIDAD', 'unit_price' => 415.00],
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
