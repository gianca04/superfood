<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Client;
use App\Models\Project;
use Carbon\Carbon;
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

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            //IMPORTACION POR CSV
            //DESCARGA DE PLANTILLA CSV
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
                        ->content(fn(Get $get) => $this->previewCsv($get))
                        ->hidden(fn(Get $get) => !$get('archivo_csv')),
                ])
                // ESTO ES LO QUE FALTA:
                ->action(function (array $data) {
                    $this->importCsv($data);
                }),

        ];
    }
    protected function descargarPlantilla(): StreamedResponse
    {
        return response()->streamDownload(function () {

            // BOM para Excel (evita problemas con acentos)
            echo "\xEF\xBB\xBF";

            $handle = fopen('php://output', 'w');

            // CABECERAS
            fputcsv($handle, [
                'service_code',
                'request_number',
                'service_start_date',
                'name',
                'client_id',
                'sub_client_id',
                'amount',
                'work_order_number',
                'task_type',
                'has_quote',
                'has_report',
                'fracttal_status',
                'purchase_order',
                'migo_code',
                'status',
                'quote_sent_at',
                'final_comments',
                'acta_conformidad',
            ], ';');

            // FILA DE EJEMPLO
            fputcsv($handle, [
                'SRV-001',
                'REQ-2025-0001',
                '2025-01-15',
                'Mantenimiento preventivo cafeteras',
                1,
                3,
                1500.00,
                'OT-4587',
                'Mantenimiento',
                'SI',
                'SI',
                'Pendiente',
                'OC-8899',
                'MIGO-3321',
                'Pendiente',
                '2025-01-16 10:30:00',
                'Trabajo programado sin observaciones',
                'NO'
            ], ';');

            fclose($handle);
        }, 'plantilla_importacion_proyectos.csv');
    }
    protected function v($value)
    {
        return ($value === null || $value === '')
            ? "<span style='color:#9ca3af;font-style:italic'>null</span>"
            : e($value);
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

        // Detectar delimitador
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',')
            ? ';'
            : ',';

        // Header
        $header = fgetcsv($handle, 1000, $delimiter);
        $header = array_map(
            fn($h) => strtolower(trim(preg_replace('/[\x{FEFF}]+/u', '', $h))),
            $header
        );

        $required = ['name', 'client_id', 'sub_client_id'];
        $visualColumns = [
            'service_code',
            'request_number',
            'service_start_date',
            'amount',
            'work_order_number',
            'task_type',
            'has_quote',
            'has_report',
            'fracttal_status',
            'purchase_order',
            'migo_code',
            'status',
            'quote_sent_at',
            'final_comments',
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

        // Evitar undefined index en columnas visuales
        foreach ($visualColumns as $col) {
            if (!array_key_exists($col, $idx)) {
                $idx[$col] = null;
            }
        }


        // Tabla HTML
        $html = '<div style="overflow-x:auto;border:1px solid #e5e7eb;border-radius:8px">';
        $html .= '
<table style="
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    font-size:13px;
">
';
        $html .= '
<thead>
<tr style="background:#f3f4f6">
    <th style="padding:10px;border:1px solid #e5e7eb">Service Code</th>
    <th style="border:1px solid #e5e7eb">Request #</th>
    <th style="border:1px solid #e5e7eb">Inicio</th>
    <th style="border:1px solid #e5e7eb">Proyecto</th>
    <th style="border:1px solid #e5e7eb">Cliente</th>
    <th style="border:1px solid #e5e7eb">Subcliente</th>
    <th style="border:1px solid #e5e7eb">Monto</th>
    <th style="border:1px solid #e5e7eb">OT</th>
    <th style="border:1px solid #e5e7eb">Tipo</th>
    <th style="border:1px solid #e5e7eb">Cot.</th>
    <th style="border:1px solid #e5e7eb">Rep.</th>
    <th style="border:1px solid #e5e7eb">Fracttal</th>
    <th style="border:1px solid #e5e7eb">OC</th>
    <th style="border:1px solid #e5e7eb">MIGO</th>
    <th style="border:1px solid #e5e7eb">Estado</th>
    <th style="border:1px solid #e5e7eb">Cot. Enviada</th>
    <th style="border:1px solid #e5e7eb">Comentarios</th>
    <th style="border:1px solid #e5e7eb">¿Acta de conformidad?</th>
    <th style="border:1px solid #e5e7eb">Acción</th>
</tr>
</thead>
<tbody>';

        $limit = 30;
        $count = 0;

        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false && $count < $limit) {

            /* ========= VALORES BASE ========= */
            $name        = trim($row[$idx['name']] ?? '');
            $clientValue = trim($row[$idx['client_id']] ?? '');
            $subValue    = trim($row[$idx['sub_client_id']] ?? '');

            $serviceCode = $idx['service_code'] !== null ? trim($row[$idx['service_code']] ?? '') : null;
            $requestNum  = $idx['request_number'] !== null ? trim($row[$idx['request_number']] ?? '') : null;

            /* ========= FECHA ========= */
            $startDateRaw = $idx['service_start_date'] !== null ? trim($row[$idx['service_start_date']] ?? '') : '';
            $startDate = null;

            if ($startDateRaw) {
                try {
                    $startDate = str_contains($startDateRaw, '/')
                        ? Carbon::createFromFormat('d/m/Y', $startDateRaw)->format('Y-m-d')
                        : Carbon::parse($startDateRaw)->format('Y-m-d');
                } catch (\Exception $e) {
                    $startDate = 'Fecha inválida';
                }
            }

            /* ========= OPCIONALES ========= */
            $amount          = $idx['amount'] !== null ? ($row[$idx['amount']] !== '' ? trim($row[$idx['amount']]) : null) : null;
            $workOrder       = $idx['work_order_number'] !== null ? ($row[$idx['work_order_number']] !== '' ? trim($row[$idx['work_order_number']]) : null) : null;
            $taskType        = $idx['task_type'] !== null ? ($row[$idx['task_type']] !== '' ? trim($row[$idx['task_type']]) : null) : null;
            $hasQuote        = $idx['has_quote'] !== null ? ($row[$idx['has_quote']] !== '' ? trim($row[$idx['has_quote']]) : null) : null;
            $hasReport       = $idx['has_report'] !== null ? ($row[$idx['has_report']] !== '' ? trim($row[$idx['has_report']]) : null) : null;
            $fracttalStatus  = $idx['fracttal_status'] !== null ? ($row[$idx['fracttal_status']] !== '' ? trim($row[$idx['fracttal_status']]) : null) : null;
            $purchaseOrder   = $idx['purchase_order'] !== null ? ($row[$idx['purchase_order']] !== '' ? trim($row[$idx['purchase_order']]) : null) : null;
            $migoCode        = $idx['migo_code'] !== null ? ($row[$idx['migo_code']] !== '' ? trim($row[$idx['migo_code']]) : null) : null;
            $status          = $idx['status'] !== null ? ($row[$idx['status']] !== '' ? trim($row[$idx['status']]) : null) : null;
            $finalComments   = $idx['final_comments'] !== null ? ($row[$idx['final_comments']] !== '' ? trim($row[$idx['final_comments']]) : null) : null;

            /* ========= ESTADO POR DEFECTO ========= */
            $estado = 'Omitir (Campos incompletos)';
            $color  = 'red';
            $clientName = $clientValue;
            $subName    = $subValue;

            /* ========= VALIDACIÓN ========= */
            if ($name && $clientValue && $subValue) {

                /* CLIENT */
                $client = is_numeric($clientValue)
                    ? Client::find($clientValue)
                    : Client::where('business_name', $clientValue)->first();

                if ($client) {
                    $clientName = $client->business_name;

                    /* SUBCLIENT SOLO DEL CLIENTE */
                    $subClient = $client->subClients()
                        ->where(is_numeric($subValue) ? 'id' : 'name', $subValue)
                        ->first();

                    if ($subClient) {
                        $subName = $subClient->name;

                        /* PROJECT */
                        $project = Project::where('name', $name)
                            ->where('sub_client_id', $subClient->id)
                            ->first();

                        if ($project) {
                            $estado = 'Actualizar';
                            $color  = 'yellow';
                        } else {
                            $estado = 'Crear';
                            $color  = 'green';
                        }
                    } else {
                        $estado = 'Omitir (Subcliente no pertenece al cliente)';
                    }
                } else {
                    $estado = 'Omitir (Cliente no existe)';
                }
            }

            /* ========= ACTA DE CONFORMIDAD (Lógica de valor por defecto) ========= */
            $actaRaw = $idx['acta_conformidad'] !== null ? trim($row[$idx['acta_conformidad']] ?? '') : '';
            // Si es null o vacío, por defecto es 'no'
            $actaConformidad = empty($actaRaw) ? 'no' : strtolower($actaRaw);
            /* ========= PINTAR FILA ========= */
            $html .= $this->previewRow(
                $name,
                $serviceCode,
                $requestNum,
                $startDate,
                $amount,
                $workOrder,
                $taskType,
                $hasQuote,
                $hasReport,
                $fracttalStatus,
                $purchaseOrder,
                $migoCode,
                $status,
                null, // quoteSentAt
                $finalComments,
                $clientName,
                $subName,
                $actaConformidad, // <--- Enviamos el nuevo valor
                $estado,
                $color
            );

            $count++;
        }


        fclose($handle);

        $html .= '</tbody></table></div>';
        $html .= "<div style='padding:8px;font-size:12px;color:#6b7280'>
        Mostrando hasta {$limit} filas del archivo
    </div>";

        return new HtmlString($html);
    }
    protected function previewRow(
        $name,
        $serviceCode,
        $requestNum,
        $startDate,
        $amount,
        $workOrder,
        $taskType,
        $hasQuote,
        $hasReport,
        $fracttalStatus,
        $purchaseOrder,
        $migoCode,
        $status,
        $quoteSentAt,
        $finalComments,
        $client,
        $sub,
        $actaConformidad, // <--- Asegúrate de recibirlo aquí
        $text,
        $color
    ): string {

        $colors = [
            'green'  => 'background:#dcfce7;color:#166534',
            'yellow' => 'background:#fef3c7;color:#92400e',
            'red'    => 'background:#fee2e2;color:#991b1b',
        ];
        // Estilo especial para la columna del acta
        $actaStyle = (strtolower($actaConformidad) === 'si' || strtolower($actaConformidad) === 'sí')
            ? 'color:#16a34a;font-weight:bold;' // Verde si es SI
            : 'color:#6b7280;';               // Gris si es NO

        return "
<tr>
    <td>{$this->v($serviceCode)}</td>
    <td>{$this->v($requestNum)}</td>
    <td>{$this->v($startDate)}</td>
    <td style='font-weight:500'>{$this->v($name)}</td>
    <td>{$this->v($client)}</td>
    <td>{$this->v($sub)}</td>
    <td style='text-align:right'>{$this->v($amount)}</td>
    <td>{$this->v($workOrder)}</td>
    <td>{$this->v($taskType)}</td>
    <td>{$this->v($hasQuote)}</td>
    <td>{$this->v($hasReport)}</td>
    <td>{$this->v($fracttalStatus)}</td>
    <td>{$this->v($purchaseOrder)}</td>
    <td>{$this->v($migoCode)}</td>
    <td>{$this->v($status)}</td>
    <td>{$this->v($quoteSentAt)}</td>
    <td style='max-width:260px;white-space:pre-wrap'>{$this->v($finalComments)}</td>
    <td style='{$actaStyle}'>{$this->v(strtoupper($actaConformidad))}</td>
    <td>
        <span style='padding:4px 10px;border-radius:6px;font-weight:600;{$colors[$color]}'>
            {$text}
        </span>
    </td>
</tr>
";
    }
    public function importCsv(array $data)
    {
        $archivo = $data['archivo_csv'] ?? null;
        if (!$archivo) return;

        $path = Storage::disk('local')->path($archivo);
        $handle = fopen($path, 'r');
        $firstLine = fgets($handle);
        rewind($handle);
        $actasCreadas = 0;
        $created = $updated = $omitted = 0;
        $actasCreadas = 0;


        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $header = fgetcsv($handle, 1000, $delimiter);
        $header = array_map(fn($h) => strtolower(trim(preg_replace('/[\x{FEFF}]+/u', '', $h))), $header);
        $idx = array_flip($header);

        $created = $updated = $omitted = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (empty(array_filter($row))) continue;

                $name        = trim($row[$idx['name']] ?? '');
                $clientValue = trim($row[$idx['client_id']] ?? '');
                $subValue    = trim($row[$idx['sub_client_id']] ?? '');

                if (!$name || !$clientValue || !$subValue) {
                    $omitted++;
                    continue;
                }

                $decision = $this->resolveImportAction($name, $clientValue, $subValue);
                if ($decision['action'] === 'omit') {
                    $omitted++;
                    continue;
                }

                // Preparar datos del Proyecto
                $payload = [
                    'name'          => $name,
                    'sub_client_id' => $decision['sub']->id,
                ];

                // Mapeo automático de columnas existentes en el CSV
                $columns = [
                    'service_code',
                    'request_number',
                    'amount',
                    'work_order_number',
                    'task_type',
                    'has_quote',
                    'has_report',
                    'fracttal_status',
                    'purchase_order',
                    'migo_code',
                    'status',
                    'final_comments'
                ];

                foreach ($columns as $col) {
                    if (isset($idx[$col])) {
                        $payload[$col] = trim($row[$idx[$col]]) ?: null;
                    }
                }

                // Lógica de Acta de Conformidad
                $valorActa = isset($idx['acta_conformidad']) ? strtolower(trim($row[$idx['acta_conformidad']])) : 'no';
                $debeCrearActa = ($valorActa === 'si' || $valorActa === 'sí');

                if ($debeCrearActa) {
                    $payload['status'] = 'Aprobado';
                }

                // Crear o Actualizar Proyecto
                if ($decision['action'] === 'create') {
                    $project = Project::create($payload);
                    $created++;
                } else {
                    $project = $decision['project'];
                    $project->update($payload);
                    $updated++;
                }



                // CREACIÓN DEL ACTA (Compliance)
                $acta = \App\Models\Compliance::firstOrCreate(
                    ['project_id' => $project->id],
                    [
                        'state' => 'Pendiente',
                        'assets' => [],
                        'maintenance_observations' => $payload['final_comments'] ?? null,
                    ]
                );

                if ($acta->wasRecentlyCreated) {
                    $actasCreadas++;
                }
            }

            DB::commit();
            Notification::make()
                ->title('Importación completada')
                ->body(
                    "Proyectos creados: {$created}\n" .
                        "Proyectos actualizados: {$updated}\n" .
                        "Actas creadas/actualizadas: {$actasCreadas}\n" .
                        "Filas omitidas: {$omitted}"
                )
                ->success()
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
        fclose($handle);
    }
    protected function resolveImportAction(
        string $name,
        string $clientValue,
        string $subValue
    ): array {

        /* ===== CLIENTE ===== */
        $client = is_numeric($clientValue)
            ? Client::find($clientValue)
            : Client::where('business_name', $clientValue)->first();

        if (!$client) {
            return ['action' => 'omit', 'reason' => 'Cliente no existe'];
        }

        /* ===== SUBCLIENTE SOLO DEL CLIENTE ===== */
        $subClientQuery = $client->subClients();

        $subClient = is_numeric($subValue)
            ? $subClientQuery->where('id', $subValue)->first()
            : $subClientQuery->where('name', $subValue)->first();

        if (!$subClient) {
            return ['action' => 'omit', 'reason' => 'Subcliente no pertenece al cliente'];
        }

        /* ===== PROYECTO ===== */
        $project = Project::where('name', $name)
            ->where('sub_client_id', $subClient->id)
            ->first();

        if ($project) {
            return [
                'action'   => 'update',
                'client'   => $client,
                'sub'      => $subClient,
                'project'  => $project,
            ];
        }

        return [
            'action'  => 'create',
            'client'  => $client,
            'sub'     => $subClient,
        ];
    }
}
