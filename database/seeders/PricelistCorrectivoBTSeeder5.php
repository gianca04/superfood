<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pricelist;
use App\Models\Unit;
use App\Models\PriceType;

class PricelistCorrectivoBTSeeder5 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el tipo de precio "Mantenimiento Correctivos Baja Tensión"
        $priceType = PriceType::firstOrCreate(['name' => 'Mantenimiento Correctivos Baja Tensión']);

        // Datos de Mantenimiento Correctivos Baja Tensión - Parte 5 (Final)
        $pricelists = [
            ['sat_line' => 'MCBT501', 'sat_description' => 'TOMA 16 A (3P+T) - 250 V (AZUL) 6H _ MENNEKES _ 278 _ IP67', 'unit' => 'UNIDAD', 'unit_price' => 78.00],
            ['sat_line' => 'MCBT502', 'sat_description' => 'TOMA 16 A (3P+T+N) - 250 V (AZUL) 6H _ MENNEKES _ 278 _ IP67', 'unit' => 'UNIDAD', 'unit_price' => 48.20],
            ['sat_line' => 'MCBT503', 'sat_description' => 'TOMA 32 A (2P+T) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP44', 'unit' => 'UNIDAD', 'unit_price' => 49.78],
            ['sat_line' => 'MCBT504', 'sat_description' => 'TOMA 32 A (3P+T) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP44', 'unit' => 'UNIDAD', 'unit_price' => 44.00],
            ['sat_line' => 'MCBT505', 'sat_description' => 'TOMA 32 A (3P+T+N) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP44', 'unit' => 'UNIDAD', 'unit_price' => 78.64],
            ['sat_line' => 'MCBT506', 'sat_description' => 'TOMA 63 A (2P+T) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP45', 'unit' => 'UNIDAD', 'unit_price' => 171.00],
            ['sat_line' => 'MCBT507', 'sat_description' => 'TOMA 63 A (3P+T) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP45', 'unit' => 'UNIDAD', 'unit_price' => 164.00],
            ['sat_line' => 'MCBT508', 'sat_description' => 'TOMA 63 A (3P+T+N) - 250 V (AZUL) 6H  _ MENNEKES _ 260 _ IP45', 'unit' => 'UNIDAD', 'unit_price' => 180.00],
            ['sat_line' => 'MCBT509', 'sat_description' => 'TOMACORRIENTE 2P+T 10/16A - TIPO BIPASO - MODUS PLUS MINK _ TICINO _ 1180MIX _ 250 V', 'unit' => 'UNIDAD', 'unit_price' => 12.18],
            ['sat_line' => 'MCBT510', 'sat_description' => 'TOMACORRIENTE DOBLE 2P UNIVERSAL - MODUS PLUS - (COLOR BLANCO) _ TICINO _ 1212WB _ 16 A - 250 V', 'unit' => 'UNIDAD', 'unit_price' => 28.31],
            ['sat_line' => 'MCBT511', 'sat_description' => 'TOMACORRIENTE DOBLE 2P+T AMERICANO - MODUS PLUS _ TICINO _ 1228MAX _ 15 A - 250 VAC', 'unit' => 'UNIDAD', 'unit_price' => 18.87],
            ['sat_line' => 'MCBT512', 'sat_description' => 'TOMACORRIENTE SIMPLE + PLACA 503/1SR _ TICINO _ 5025 _ 15 A - 250 V - MÁS PLACA  (BLANCO - CREMA)', 'unit' => 'UNIDAD', 'unit_price' => 38.00],
            ['sat_line' => 'MCBT513', 'sat_description' => 'TOROIDE CERRADO PARA PROTECCIÓN DE CORRIENTE RESIDUAL SA, Ø 200 MM', 'unit' => 'UNIDAD', 'unit_price' => 641.50],
            ['sat_line' => 'MCBT514', 'sat_description' => 'TOROIDE RECTANGULAR, 1600 A, 280 X 115 MM, PARA VIGILOHM, VIGIREX', 'unit' => 'UNIDAD', 'unit_price' => 0.00],
            ['sat_line' => 'MCBT515', 'sat_description' => 'TRANSFORMADOR DE AISLAMIENTO 220/380V 20KVA FACTOR K-13', 'unit' => 'UNIDAD', 'unit_price' => 0.00],
            ['sat_line' => 'MCBT516', 'sat_description' => 'TRANSFORMADOR DE AISLAMIENTO 220/380V 35KVA FACTOR K-13', 'unit' => 'UNIDAD', 'unit_price' => 0.00],
            ['sat_line' => 'MCBT517', 'sat_description' => 'TRANSFORMADOR DE AISLAMIENTO TRIFÁSICO - 10 KVA, K13', 'unit' => 'UNIDAD', 'unit_price' => 2533.90],
            ['sat_line' => 'MCBT518', 'sat_description' => 'TRANSFORMADOR DE AISLAMIENTO TRIFÁSICO - 3 KVA , K13', 'unit' => 'UNIDAD', 'unit_price' => 1175.20],
            ['sat_line' => 'MCBT519', 'sat_description' => 'TRANSFORMADOR DE AISLAMIENTO TRIFÁSICO - 4 KVA, K13', 'unit' => 'UNIDAD', 'unit_price' => 1476.48],
            ['sat_line' => 'MCBT520', 'sat_description' => 'TRANSFORMADOR DE AISLAMIENTO TRIFÁSICO - 6 KVA, K13', 'unit' => 'UNIDAD', 'unit_price' => 2135.59],
            ['sat_line' => 'MCBT521', 'sat_description' => 'TRANSFORMADOR DE CORRIENTE 100 A / 5 A SCHNEIDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 104.86],
            ['sat_line' => 'MCBT522', 'sat_description' => 'TRANSFORMADOR DE CORRIENTE 200 A / 5 A SCHNEIDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 113.00],
            ['sat_line' => 'MCBT523', 'sat_description' => 'TRANSFORMADOR DE CORRIENTE 400 A / 5 A SCHNEIDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 125.00],
            ['sat_line' => 'MCBT524', 'sat_description' => 'TRANSFORMADOR DE CORRIENTE 75 A / 5 A SCHNEIDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 78.64],
            ['sat_line' => 'MCBT525', 'sat_description' => 'TRANSFORMADOR DE CORRIENTE DE DOBLE NÚCLEO POWERLOGIC - TIPO GG, PARA BARRA - 800 A / 5 A SCHNEIDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 195.70],
            ['sat_line' => 'MCBT526', 'sat_description' => 'TRANSFORMADOR DE CORRIENTE DE DOBLE NÚCLEO POWERLOGIC - TIPO GJ, PARA BARRA - 1000 A / 5 A SCHNEIDER ELECTRIC', 'unit' => 'UNIDAD', 'unit_price' => 285.00],
            ['sat_line' => 'MCBT527', 'sat_description' => 'TRANSFORMADORES DE CORRIENTE DE NUCLEO PARTIDO 5 /100', 'unit' => 'UNIDAD', 'unit_price' => 162.00],
            ['sat_line' => 'MCBT528', 'sat_description' => 'TRANSFORMADORES DE CORRIENTE DE NUCLEO PARTIDO 5 /1000', 'unit' => 'UNIDAD', 'unit_price' => 272.00],
            ['sat_line' => 'MCBT529', 'sat_description' => 'TRANSFORMADORES DE CORRIENTE DE NUCLEO PARTIDO 5 /200', 'unit' => 'UNIDAD', 'unit_price' => 162.00],
            ['sat_line' => 'MCBT530', 'sat_description' => 'TRANSFORMADORES DE CORRIENTE DE NUCLEO PARTIDO 5 /300', 'unit' => 'UNIDAD', 'unit_price' => 162.00],
            ['sat_line' => 'MCBT531', 'sat_description' => 'TRANSFORMADORES DE CORRIENTE DE NUCLEO PARTIDO 5 /400', 'unit' => 'UNIDAD', 'unit_price' => 204.00],
            ['sat_line' => 'MCBT532', 'sat_description' => 'TRANSFORMADORES DE CORRIENTE DE NUCLEO PARTIDO 5 /500', 'unit' => 'UNIDAD', 'unit_price' => 267.42],
            ['sat_line' => 'MCBT533', 'sat_description' => 'TUBERIA  _ 1 1/2" EMT', 'unit' => 'ML', 'unit_price' => 18.00],
            ['sat_line' => 'MCBT534', 'sat_description' => 'TUBERIA  _ 2" EMT', 'unit' => 'ML', 'unit_price' => 23.00],
            ['sat_line' => 'MCBT535', 'sat_description' => 'TUBERIA  _ 3" EMT', 'unit' => 'ML', 'unit_price' => 33.90],
            ['sat_line' => 'MCBT536', 'sat_description' => 'TUBERIA  _ 4" EMT', 'unit' => 'ML', 'unit_price' => 56.73],
            ['sat_line' => 'MCBT537', 'sat_description' => 'TUBERIA _ 1" EMT', 'unit' => 'ML', 'unit_price' => 12.44],
            ['sat_line' => 'MCBT538', 'sat_description' => 'TUBERIA _ 3/4" EMT', 'unit' => 'ML', 'unit_price' => 8.88],
            ['sat_line' => 'MCBT539', 'sat_description' => 'TUBERIA DE PVC - SAP -  4"', 'unit' => 'ML', 'unit_price' => 24.17],
            ['sat_line' => 'MCBT540', 'sat_description' => 'TUBERIA DE PVC - SAP - 1 1/2"', 'unit' => 'ML', 'unit_price' => 10.45],
            ['sat_line' => 'MCBT541', 'sat_description' => 'TUBERIA DE PVC - SAP - 1"', 'unit' => 'ML', 'unit_price' => 6.29],
            ['sat_line' => 'MCBT542', 'sat_description' => 'TUBERIA DE PVC - SAP - 1/2"', 'unit' => 'ML', 'unit_price' => 5.76],
            ['sat_line' => 'MCBT543', 'sat_description' => 'TUBERIA DE PVC - SAP - 2"', 'unit' => 'ML', 'unit_price' => 15.68],
            ['sat_line' => 'MCBT544', 'sat_description' => 'TUBERIA DE PVC - SAP - 3/4"', 'unit' => 'ML', 'unit_price' => 4.70],
            ['sat_line' => 'MCBT545', 'sat_description' => 'TUBERIA FLEXIBLE (GRIS CERRADA)  PVC - BG-26P KSS _ 3/4"', 'unit' => 'ML', 'unit_price' => 4.13],
            ['sat_line' => 'MCBT546', 'sat_description' => 'TUBERIA FLEXIBLE (GRIS CERRADA) PVC - BG-22P KSS _ 1/2"', 'unit' => 'ML', 'unit_price' => 3.22],
            ['sat_line' => 'MCBT547', 'sat_description' => 'TUBERIA FLEXIBLE (GRIS CERRADA) PVC - BG-34P KSS _ 1"', 'unit' => 'ML', 'unit_price' => 9.02],
            ['sat_line' => 'MCBT548', 'sat_description' => 'TUBERIA FLEXIBLE DE FIERRO GALVANIZADO / CON FORRO DE PVC _ 1 1/2"', 'unit' => 'ML', 'unit_price' => 15.99],
            ['sat_line' => 'MCBT549', 'sat_description' => 'TUBERIA FLEXIBLE DE FIERRO GALVANIZADO / CON FORRO DE PVC _ 1"', 'unit' => 'ML', 'unit_price' => 12.10],
            ['sat_line' => 'MCBT550', 'sat_description' => 'TUBERIA FLEXIBLE DE FIERRO GALVANIZADO / CON FORRO DE PVC _ 2"', 'unit' => 'ML', 'unit_price' => 36.46],
            ['sat_line' => 'MCBT551', 'sat_description' => 'TUBERIA FLEXIBLE DE FIERRO GALVANIZADO / CON FORRO DE PVC _ 3/4"', 'unit' => 'ML', 'unit_price' => 9.70],
            ['sat_line' => 'MCBT552', 'sat_description' => 'UNIÓN  _ 1 1/2" EMT', 'unit' => 'UNIDAD', 'unit_price' => 5.10],
            ['sat_line' => 'MCBT553', 'sat_description' => 'UNIÓN  _ 2" EMT', 'unit' => 'UNIDAD', 'unit_price' => 7.70],
            ['sat_line' => 'MCBT554', 'sat_description' => 'UNIÓN  _ 3" EMT', 'unit' => 'UNIDAD', 'unit_price' => 18.69],
            ['sat_line' => 'MCBT555', 'sat_description' => 'UNIÓN  _ 4" EMT', 'unit' => 'UNIDAD', 'unit_price' => 28.90],
            ['sat_line' => 'MCBT556', 'sat_description' => 'UNION DE CANALETA DE 110 X 50 MM 10094RBR EFAPEL', 'unit' => 'UNIDAD', 'unit_price' => 8.17],
            ['sat_line' => 'MCBT557', 'sat_description' => 'UNIÓN DE CANALETA DE 20 X 12.5 MM EFAPHEL', 'unit' => 'UNIDAD', 'unit_price' => 7.10],
            ['sat_line' => 'MCBT558', 'sat_description' => 'UNIÓN DE CANALETA DE 50 X 20 MM LEGRAND DLP-S 638166', 'unit' => 'UNIDAD', 'unit_price' => 8.20],
            ['sat_line' => 'MCBT559', 'sat_description' => 'UNION DE PVC (LUZ) SAP _ 1 1/2"', 'unit' => 'UNIDAD', 'unit_price' => 5.40],
            ['sat_line' => 'MCBT560', 'sat_description' => 'UNION DE PVC (LUZ) SAP _ 1"', 'unit' => 'UNIDAD', 'unit_price' => 2.50],
            ['sat_line' => 'MCBT561', 'sat_description' => 'UNION DE PVC (LUZ) SAP _ 1/2"', 'unit' => 'UNIDAD', 'unit_price' => 0.78],
            ['sat_line' => 'MCBT562', 'sat_description' => 'UNION DE PVC (LUZ) SAP _ 2"', 'unit' => 'UNIDAD', 'unit_price' => 3.87],
            ['sat_line' => 'MCBT563', 'sat_description' => 'UNION DE PVC (LUZ) SAP _ 3/4"', 'unit' => 'UNIDAD', 'unit_price' => 0.90],
            ['sat_line' => 'MCBT564', 'sat_description' => 'UNIÓN MIXTA - 1 1/2" SAP', 'unit' => 'UNIDAD', 'unit_price' => 6.20],
            ['sat_line' => 'MCBT565', 'sat_description' => 'UNIÓN MIXTA - 1" SAP', 'unit' => 'UNIDAD', 'unit_price' => 2.93],
            ['sat_line' => 'MCBT566', 'sat_description' => 'UNIÓN MIXTA - 2" SAP', 'unit' => 'UNIDAD', 'unit_price' => 4.04],
            ['sat_line' => 'MCBT567', 'sat_description' => 'UNIÓN MIXTA - 3/4" SAP', 'unit' => 'UNIDAD', 'unit_price' => 1.41],
            ['sat_line' => 'MCBT568', 'sat_description' => 'UNIÓN_ 1" EMT', 'unit' => 'UNIDAD', 'unit_price' => 1.80],
            ['sat_line' => 'MCBT569', 'sat_description' => 'UNIÓN_ 3/4" EMT', 'unit' => 'UNIDAD', 'unit_price' => 1.14],
            ['sat_line' => 'MCBT570', 'sat_description' => 'VARILLA DE COBRE ELECTROLITICO P/ LINEA A TIERRA DE 3/4" X 2.40 M', 'unit' => 'UNIDAD', 'unit_price' => 367.00],
            ['sat_line' => 'MCBT571', 'sat_description' => 'VENTILADOR - SCHNEIDER - MODELO NSYCVF85M230PF / 50 A 60 HZ /0.121/0.097/17-15WATTS', 'unit' => 'UNIDAD', 'unit_price' => 591.00],
            ['sat_line' => 'MCBT572', 'sat_description' => 'AUDITORIAS ENERGÉTICAS SEGÚN ANEXO 9.', 'unit' => 'SEDE', 'unit_price' => 240.10],
            ['sat_line' => 'MCBT573', 'sat_description' => 'SERVIDOR IHOST-HUB -ZIGBEE 3,0, MARCA SONOFF - PUERTA DE ENLACE AIBRIDGE, SERVIDOR DE HOST LOCAL Y ALMACENAMIENTO DE DATOS, WIFI, LAN, CONTROL DE ESCENA - 4GB', 'unit' => 'UNIDAD', 'unit_price' => 1124.76],
            ['sat_line' => 'MCBT574', 'sat_description' => 'INTERRUPTOR DE PARED NSPANEL, MARCA - SONOFF - PANEL DE CONTROL TODO EN UNO HMI, FUNCIONA CON ALEXA, GOOGLE HOME, SIRI, ALICE', 'unit' => 'UNIDAD', 'unit_price' => 651.35],
            ['sat_line' => 'MCBT575', 'sat_description' => 'WAVLINK-REPET_WIFI INALÁMB_ALTA POTEN_EXTERIORES_POE, N300 /2,4 HIGH POWER', 'unit' => 'UNIDAD', 'unit_price' => 458.83],
            ['sat_line' => 'MCBT576', 'sat_description' => 'WAVLINK-REPET_WIFI INALÁMB_ALTA POTEN _EXTERIORES_POE, AC600/2,5 HIGH POWER', 'unit' => 'UNIDAD', 'unit_price' => 629.45],
            ['sat_line' => 'MCBT577', 'sat_description' => 'WAVLINK-REPET_WIFI INALÁMB_ALTA POTEN _EXTERIORES_ POE, AC1200/2,6', 'unit' => 'UNIDAD', 'unit_price' => 957.12],
            ['sat_line' => 'MCBT578', 'sat_description' => 'REPETIDOR WIFI DE 1200MBPS, AMPLIFICADOR INALÁMBRICO DE BANDA DUAL, RED DE 2,4G Y 5GHZ, AMPLIFICADOR DE SEÑAL DE LARGO ALCANCE PARA EL HOGAR Y LA OFICINA', 'unit' => 'UNIDAD', 'unit_price' => 409.31],
            ['sat_line' => 'MCBT579', 'sat_description' => 'SONOFF-MINIR4M INTERRUPTOR INTELIGENTE WIFI, MINIMÓDULO DE AUTOMATIZACIÓN, RELÉ DE CONEXIÓN LOCAL, FUNCIONA CON ALEXA, GOOGLE HOME, EWELINK', 'unit' => 'UNIDAD', 'unit_price' => 148.40],
            ['sat_line' => 'MCBT580', 'sat_description' => 'SONOFF TH ELITE-INTERRUPTOR INTELIGENTE, DISPOSITIVO DE CONTROL INTELIGENTE DE TEMPERATURA Y HUMEDAD CON PANTALLA LCD, MODO AUTOMÁTICO 20A', 'unit' => 'UNIDAD', 'unit_price' => 256.20],
            ['sat_line' => 'MCBT581', 'sat_description' => 'SONOFF-INTERRUPTOR DE MEDIDOR DE POTENCIA INTELIGENTE, DISPOSITIVO CON WIFI, PANTALLA LCD, FUNCIONA CON LA APLICACIÓN EWELINK, ALEXA Y GOOGLE HOME, POW ELITE, 20A', 'unit' => 'UNIDAD', 'unit_price' => 151.20],
            ['sat_line' => 'MCBT582', 'sat_description' => 'EWELINK-CÁMARA INTELIGENTE IMPERMEABLE IP66, INTERCOMUNICADOR DE AUDIO BIDIRECCIONAL, VISIÓN NOCTURNA, IR, LED, 1080P, PARA EXTERIORES', 'unit' => 'UNIDAD', 'unit_price' => 273.00],
            ['sat_line' => 'MCBT583', 'sat_description' => 'SONOFF-MEDIDOR DE POTENCIA APILABLE SPM-MAIN/MONITOREO DE CONSUMO DE ENERGÍA A TRAVÉS DE LA VERIFICACIÓN DE LA APLICACIÓN EWELINK', 'unit' => 'UNIDAD', 'unit_price' => 305.20],
            ['sat_line' => 'MCBT584', 'sat_description' => 'SONOFF-MEDIDOR DE POTENCIA APILABLE SPM-MAIN/4 RELÉS, 20A/GANG, PROTECCIÓN CONTRA SOBRECARGA, MONITOREO DE CONSUMO DE ENERGÍA A TRAVÉS DE LA VERIFICACIÓN DE LA APLICACIÓN EWELINK', 'unit' => 'UNIDAD', 'unit_price' => 504.00],
            ['sat_line' => 'MCBT585', 'sat_description' => 'MISOL-ESTACIÓN METEOROLÓGICA INALÁMBRICA, DISPOSITIVO CON CONEXIÓN WIFI, CARGA DE DATOS A LA WEB (WUNDERGROUND), HP2550-1', 'unit' => 'UNIDAD', 'unit_price' => 3690.67],
            ['sat_line' => 'MCBT586', 'sat_description' => 'SONOFF SNZB-06P SENSOR DE PRESENCIA ZIGBEE', 'unit' => 'UNIDAD', 'unit_price' => 126.00],
            ['sat_line' => 'MCBT587', 'sat_description' => 'ZIGBEE-DETECTOR DE PRESENCIA HUMANA MMWAVE, RADAR CON SENSOR DE MOVIMIENTO, 220V/110V, 5,8G/24G, RELÉ LUX, DETECCIÓN DE LUZ/DISTANCIA, TUYA SMART LIFE', 'unit' => 'UNIDAD', 'unit_price' => 240.00],
            ['sat_line' => 'MCBT588', 'sat_description' => 'ALQUILER ANDAMIO CERTIFICADO DE 2 CUERPOS', 'unit' => 'DIA', 'unit_price' => 106.08],
            ['sat_line' => 'MCBT589', 'sat_description' => 'ALQUILER ANDAMIO CERTIFICADO DE 3 CUERPOS', 'unit' => 'DIA', 'unit_price' => 115.00],
            ['sat_line' => 'MCBT590', 'sat_description' => 'ALQUILER ANDAMIO CERTIFICADO DE 4 CUERPOS', 'unit' => 'DIA', 'unit_price' => 189.80],
            ['sat_line' => 'MCBT591', 'sat_description' => 'Servicios auxiliares NO reccurrentes IIEE BAJA TENSIÓN', 'unit' => 'UNIDAD', 'unit_price' => 1.00],
            ['sat_line' => 'MCBT592', 'sat_description' => 'Repuestos auxiliares NO reccurrentes IIEE BAJA TENSIÓN', 'unit' => 'UNIDAD', 'unit_price' => 1.00],
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
