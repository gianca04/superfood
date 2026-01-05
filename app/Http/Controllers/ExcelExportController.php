<?php

namespace App\Http\Controllers;

use App\Models\WorkReport;
use Illuminate\Http\Request;

class ExcelExportController extends Controller
{
    public function export($id)
    {
        $workReport = WorkReport::with([
        'project.client', // Cliente a través de la cotización
        'project.subClient', // Subcliente directo
    ])->findOrFail($id);

    // Obtener datos del cliente
    $clientData = $workReport->client_data;

    // Obtener datos de la OT
    $workOrderData = $workReport->work_order_data;

    // Combinar datos
    $data = array_merge($clientData, $workOrderData);

    return response()->json($data);
    }
}
