<?php

namespace App\Filament\Resources\PricelistResource\Pages;

use App\Filament\Resources\PricelistResource;
use App\Models\Pricelist;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListPricelists extends ListRecords
{
    protected static string $resource = PricelistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('descargarPlantillaCsv')
                ->label('Descargar plantilla CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn() => $this->descargarPlantilla()),

            Actions\Action::make('importarCsv')
                ->label('Importar CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalWidth('7xl')
                ->form([
                    FileUpload::make('archivo_csv')
                        ->label('Archivo CSV')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('temp-imports')
                        ->required()
                        ->live(),

                    Placeholder::make('preview')
                        ->label('Vista previa')
                        ->content(fn($get) => $this->previewCsv($get))
                        ->hidden(fn($get) => !$get('archivo_csv')),
                ])
                ->action(function (array $data) {
                    $this->importCsv($data);
                }),
        ];
    }

    protected function descargarPlantilla(): StreamedResponse
    {
        return response()->streamDownload(function () {

            // BOM para Excel (acentos OK)
            echo "\xEF\xBB\xBF";

            $handle = fopen('php://output', 'w');

            // CABECERAS
            fputcsv($handle, [
                'LINEA SAT',
                'DESCRIPCIÓN',
                'UNIDAD',
                'PRECIO UNITARIO', // Cambiado el nombre de la columna
                'TIPO ID',
            ], ';');

            // FILA DE EJEMPLO
            fputcsv($handle, [
                'SAT-001',
                'Mantenimiento preventivo',
                'SERVICIO',
                250.00,
                'MANO DE OBRA',
            ], ';');

            fclose($handle);
        }, 'plantilla_lineas_sat.csv');
    }

    protected function v($value)
    {
        return ($value === null || $value === '')
            ? "<span style='color:#9ca3af;font-style:italic'>null</span>"
            : e($value);
    }

    protected function normalizeHeader(string $value): string
    {
        $value = preg_replace('/[\x{FEFF}]+/u', '', $value); // Eliminar caracteres BOM
        $value = trim($value); // Eliminar espacios en blanco
        $value = mb_strtolower($value, 'UTF-8'); // Convertir a minúsculas
        $value = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'], // Caracteres con tilde y ñ
            ['a', 'e', 'i', 'o', 'u', 'n'], // Equivalentes sin tilde
            $value
        );
        $value = preg_replace('/[^a-z0-9_ ]/', '', $value); // Eliminar caracteres no deseados
        $value = str_replace(' ', '_', $value); // Reemplazar espacios por guiones bajos

        return $value;
    }

    protected function previewCsv(Get $get): HtmlString|string
    {
        $archivo = $get('archivo_csv');

        if (!$archivo) {
            return 'Esperando archivo...';
        }

        if (is_array($archivo)) {
            $archivo = reset($archivo);
        }

        $path = is_string($archivo)
            ? Storage::disk('local')->path($archivo)
            : $archivo->getRealPath();

        if (!file_exists($path)) {
            return 'Cargando archivo...';
        }

        $handle = fopen($path, 'r');

        /* ========= DETECTAR DELIMITADOR ========= */
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiters = [
            ';' => substr_count($firstLine, ';'),
            ',' => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
        ];

        arsort($delimiters);
        $delimiter = array_key_first($delimiters);

        /* ========= HEADER ========= */
        $header = fgetcsv($handle, 1000, $delimiter);
        $header = array_map(
            fn($h) => $this->normalizeHeader($h),
            $header
        );

        /* ========= COLUMNAS ESPERADAS ========= */
        $required = [
            'linea_sat',
            'descripcion',
            'unidad',
            'precio_unitario',
            'tipo_id',
        ];

        foreach ($required as $col) {
            if (!in_array($col, $header)) {
                return new HtmlString(
                    "<span style='color:red;font-weight:bold'>
                    Falta la columna obligatoria: {$col}
                </span>"
                );
            }
        }

        $idx = array_flip($header);

        /* ========= TABLA HTML ========= */
        $html = '<div style="overflow-x:auto;border:1px solid #e5e7eb;border-radius:8px">';
        $html .= '
<table style="width:100%;border-collapse:separate;border-spacing:0;font-size:13px">
<thead>
<tr style="background:#f3f4f6">
    <th style="padding:10px;border:1px solid #e5e7eb">Línea SAT</th>
    <th style="border:1px solid #e5e7eb">Descripción</th>
    <th style="border:1px solid #e5e7eb">Unidad</th>
    <th style="border:1px solid #e5e7eb">Precio Unitario</th>
    <th style="border:1px solid #e5e7eb">Tipo</th>
    <th style="border:1px solid #e5e7eb">Acción</th>
</tr>
</thead>
<tbody>';

        $limit = 300;
        $count = 0;

        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false && $count < $limit) {

            $lineaSat    = trim($row[$idx['linea_sat']] ?? '');
            $descripcion = trim($row[$idx['descripcion']] ?? '');
            $unidad      = trim($row[$idx['unidad']] ?? '');
            $precio      = trim($row[$idx['precio_unitario']] ?? '');
            $tipo        = trim($row[$idx['tipo_id']] ?? '');

            // Validar y limpiar precio unitario
            if (strtolower($precio) === 'n/a') {
                $precio = null;
            } elseif (str_starts_with($precio, 'S/')) {
                $precio = trim(str_replace(['S/', ' '], '', $precio));
            }

            $estado = 'Omitir';
            $color = 'red';

            // Validar tipo_id
            $priceType = is_numeric($tipo)
                ? \App\Models\PriceType::find($tipo)
                : \App\Models\PriceType::where('name', $tipo)->first();

            if (!$priceType) {
                $estado = 'Omitir (Tipo no encontrado)';
            } else {
                // Validar unidad
                $unit = is_numeric($unidad)
                    ? \App\Models\Unit::find($unidad)
                    : \App\Models\Unit::where('name', $unidad)->first();

                if (!$unit) {
                    $estado = 'Omitir (Unidad no encontrada)';
                } else {
                    // Buscar si ya existe la línea SAT
                    $pricelist = Pricelist::where('sat_line', $lineaSat)->first();

                    if ($pricelist) {
                        $estado = 'Actualizar';
                        $color = 'yellow';
                    } elseif ($lineaSat && $descripcion) {
                        $estado = 'Crear';
                        $color = 'green';
                    } else {
                        $estado = 'Omitir (Datos incompletos)';
                    }
                }
            }

            $html .= "
<tr>
    <td style='padding:8px;border:1px solid #e5e7eb'>{$this->v($lineaSat)}</td>
    <td style='border:1px solid #e5e7eb'>{$this->v($descripcion)}</td>
    <td style='border:1px solid #e5e7eb'>" . ($unit->name ?? 'No encontrado') . "</td>
    <td style='border:1px solid #e5e7eb;text-align:right'>{$this->v($precio)}</td>
    <td style='border:1px solid #e5e7eb'>" . ($priceType->name ?? 'No encontrado') . "</td>
    <td>
        <span style='padding:4px 10px;border-radius:6px;font-weight:600;background-color:{$color};color:white'>
            {$estado}
        </span>
    </td>
</tr>";

            $count++;
        }

        fclose($handle);

        $html .= '</tbody></table></div>';
        $html .= "
    <div style='padding:8px;font-size:12px;color:#6b7280'>
        Mostrando hasta {$limit} filas del archivo
    </div>
";

        return new HtmlString($html);
    }
    protected function previewRowLineaSat(
        $lineaSat,
        $descripcion,
        $unidad,
        $precioUnitario,
        $tipo,
        $estadoTexto,
        $color
    ): string {

        $colors = [
            'green'  => 'background:#dcfce7;color:#166534',
            'yellow' => 'background:#fef3c7;color:#92400e',
            'red'    => 'background:#fee2e2;color:#991b1b',
        ];

        return "
<tr>
    <td style='font-weight:500'>{$this->v($lineaSat)}</td>
    <td style='max-width:320px;white-space:pre-wrap'>{$this->v($descripcion)}</td>
    <td>{$this->v($unidad)}</td>
    <td style='text-align:right'>{$this->v($precioUnitario)}</td>
    <td>{$this->v($tipo)}</td>
    <td>
        <span style='padding:4px 10px;border-radius:6px;font-weight:600;{$colors[$color]}'>
            {$estadoTexto}
        </span>
    </td>
</tr>
";
    }


    protected function importCsv(array $data)
    {
        $filePath = $data['archivo_csv'];

        if (!Storage::disk('local')->exists($filePath)) {
            throw new \Exception("El archivo no existe: {$filePath}");
        }

        $file = fopen(
            Storage::disk('local')->path($filePath),
            'r'
        );
        $firstLine = fgets($file);
        rewind($file);

        $delimiters = [
            ';' => substr_count($firstLine, ';'),
            ',' => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
        ];

        arsort($delimiters);
        $delimiter = array_key_first($delimiters);

        $headers = fgetcsv($file, 1000, $delimiter);
        $headers = array_map(fn($h) => $this->normalizeHeader($h), $headers);

        $idx = array_flip($headers);

        // Inicializar contadores
        $created = 0;
        $updated = 0;
        $omitted = 0;

        while (($row = fgetcsv($file, 1000, $delimiter)) !== false) {
            $rowData = array_combine($headers, $row);

            $lineaSat = $rowData['linea_sat'] ?? null;
            $descripcion = $rowData['descripcion'] ?? null;
            $unidad = $rowData['unidad'] ?? null;
            $precio = $rowData['precio_unitario'] ?? null;
            $tipo = $rowData['tipo_id'] ?? null;

            // Validar y limpiar precio unitario
            if (strtolower($precio) === 'n/a') {
                $precio = null;
            } elseif (str_starts_with($precio, 'S/') || str_starts_with($precio, 'S/')) {
                $precio = trim(str_replace(['S/', 'S/', ' '], '', $precio));
            }

            // Convertir el precio a un número decimal
            $precio = is_numeric($precio) ? (float) $precio : null;

            if (!$lineaSat || !$descripcion || !$tipo) {
                $omitted++; // Incrementar contador de omitidos
                continue; // Omitir si faltan datos obligatorios
            }

            $priceType = is_numeric($tipo)
                ? \App\Models\PriceType::find($tipo)
                : \App\Models\PriceType::where('name', $tipo)->first();

            if (!$priceType) {
                $omitted++; // Incrementar contador de omitidos
                continue; // Omitir si no se encuentra el tipo
            }

            $unit = is_numeric($unidad)
                ? \App\Models\Unit::find($unidad)
                : \App\Models\Unit::where('name', $unidad)->first();

            if (!$unit) {
                $omitted++; // Incrementar contador de omitidos
                continue; // Omitir si no se encuentra la unidad
            }

            $pricelist = Pricelist::where('sat_line', $lineaSat)->first();

            if ($pricelist) {
                // Actualizar
                $pricelist->update([
                    'sat_description' => $descripcion,
                    'unit_id' => $unit->id,
                    'unit_price' => $precio,
                    'price_type_id' => $priceType->id,
                ]);
                $updated++; // Incrementar contador de actualizados
            } else {
                // Crear
                Pricelist::create([
                    'sat_line' => $lineaSat,
                    'sat_description' => $descripcion,
                    'unit_id' => $unit->id,
                    'unit_price' => $precio,
                    'price_type_id' => $priceType->id,
                ]);
                $created++; // Incrementar contador de creados
            }
        }

        fclose($file);
        Storage::disk('local')->delete($filePath);

        // Mostrar notificación con el resumen
        Notification::make()
            ->title('Importación completada')
            ->body(
                "Registros creados: {$created}\n" .
                    "Registros actualizados: {$updated}\n" .
                    "Registros omitidos: {$omitted}"
            )
            ->success()
            ->send();
    }
}
