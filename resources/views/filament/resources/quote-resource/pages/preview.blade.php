<style>
    /* Configuración de página y Reset */
    @page {
        size: A4 landscape;
        margin: 0;
    }

    body {
        background-color: #f0f0f0;
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        font-family: Calibri, 'Trebuchet MS', sans-serif;
    }

    /* Contenedor Horizontal */
    .quotation-container {
        background-color: white;
        width: 297mm;
        min-height: 210mm;
        padding: 10mm 15mm;
        margin: 20px auto;
        box-sizing: border-box;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    @media print {
        body {
            background: white;
        }

        .quotation-container {
            margin: 0;
            width: 100%;
            box-shadow: none;
        }

        .no-print {
            display: none !important;
        }
    }

    /* Barra de Acciones */
    .action-bar {
        background: #333;
        padding: 15px;
        display: flex;
        justify-content: center;
        gap: 20px;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 5px;
        color: white;
        text-decoration: none;
        font-weight: bold;
        font-size: 14px;
        border: none;
        cursor: pointer;
    }

    .btn-back {
        background: #6c757d;
    }

    .btn-print {
        background: #0275d8;
    }

    .btn-pdf {
        background: #d9534f;
    }

    .btn-excel {
        background: #28a745;
    }

    /* Estilos de la Cotización - COLOR BLANCO */
    .q-title-section {
        background-color: #00b050 !important;
        color: white !important;
        // border: 1px solid #000; // Quitado para eliminar bordes
    }

    .q-title-table {
        width: 100%;
        border-collapse: collapse;
    }

    .q-title-table td {
        padding: 2px 15px;
        font-weight: bold;
        // border: 1px solid #000; // Quitado para eliminar bordes
        color: white !important;
    }

    /* Panel de Información en una sola fila con 3 columnas */
    .q-info-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        // border: 1px solid #000; // Quitado para eliminar bordes verticales
    }

    .q-info-table td {
        padding: 6px;
        font-size: 12.5px; // Mantener base
        vertical-align: top;
        border: none; // Mantener sin bordes
        /* Quitamos bordes internos de guía */
    }

    .q-info-table td:nth-child(1) {
        font-size: 13.5px; // Aumentar 1px para la primera columna
    }

    .q-info-table td:nth-child(2) {
        text-align: right; // Cambiado de left para alinear a la derecha
    }

    .q-info-table td:nth-child(1) span,
    .q-info-table td:nth-child(2) span {
        margin-bottom: 0px; // Cambiado a 0px para bordes pegados
        display: block;
    }

    .label-bold {
        font-weight: bold;
        width: 115px;
        display: inline-block;
        color: #000;
    }

    .box-data {
        border: 1px solid #000;
        padding: 1px 10px;
        background: white;
        display: inline-block;
        min-width: 90px;
        color: #000;
    }

    .text-blue {
        color: #0000FF !important;
        font-weight: bold;
    }

    .pink-cell {
        background-color: #fce4d6 !important; // Mantener para otros
    }

    .soft-red {
        background-color: #ffcccc !important; // Nuevo color rojo suave para Fecha ejec
    }

    .yellow-total {
        background-color: #ffff00 !important;
        font-weight: bold;
        font-size: 15px;
        text-align: center;
        border: 1px solid #000;
        padding: 3px;
        color: #000;
    }

    /* Tabla de Ítems */
    .q-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        table-layout: fixed;
        border: 1px solid #000;
    }

    .q-table th {
        background-color: #00b050 !important;
        color: white;
        border: 1px solid #000;
        padding: 8px 5px;
        font-size: 14px;
        /* Encabezado más grande */
    }

    .q-table td {
        border: 1px solid #000;
        padding: 4px;
        font-size: 11px;
        color: #000;
    }

    .row-category {
        background-color: #c6e0b4 !important;
        font-weight: bold;
    }

    .center {
        text-align: center;
    }

    .right {
        text-align: right;
    }

    .mini-table {
        // Remover estilos de mini-table ya que no se usa
    }

    .mini-table td {
        padding: 4px;
        font-size: 12px;
        border: none; // Por defecto sin borde
        text-align: center; // Centrar todo el contenido
    }

    .mini-table .label-cell {
        font-weight: bold;
        width: 70px; // Ajustado para mejor ajuste
        border: none; // Sin borde para etiquetas
    }

    .mini-table .value-cell {
        border: 1px solid #000; // Solo bordes para valores
        min-width: 100px; // Ajustado para consistencia
        // text-align: center; // Ya centrado globalmente
    }
</style>

@if (!isset($isPdf))
    <div class="action-bar no-print">
        <a href="javascript:window.close()" class="btn btn-back">Volver</a>
        <button onclick="window.print()" class="btn btn-print">Imprimir</button>
        <a href="{{ route('quotes.pdf', $original_id) }}" class="btn btn-pdf">Descargar PDF</a>
        <a href="{{ route('quotes.excel', $original_id) }}" class="btn btn-excel">Descargar EXCEL</a>
    </div>
@endif

@if (isset($isPdf))
    <style>
        @page {
            margin: 5mm;
        }

        body {
            background-color: white;
        }

        .q-table td {
            font-size: 11px;
        }

        .q-table th {
            font-size: 12px;
        }

        .q-title-table td {
            padding: 2px 15px;
        }
    </style>
@endif

<div class="quotation-container"
    @if (isset($isPdf)) style="width: 100%; max-width: 287mm; margin: 0 auto; min-height: auto; padding: 0;" @endif>
    {{-- Header Principal --}}
    <div class="q-title-section">
        <table class="q-title-table">
            <tr>
                <td align="center" style="font-size: 18px; letter-spacing: 1px; color: white; padding: 4px 15px;">
                    COTIZACIÓN DE SERVICIOS
                </td>
                <td width="150px" align="center" style="color: white;">N°</td> <!-- Quitado border-left -->
                <td width="120px" align="center" style="background: #00b050; color: white; font-size: 18px;">
                    <!-- Quitado border-left -->
                    {{ $quote_id }}
                </td>
            </tr>
        </table>
    </div>


    {{-- Panel de Información de 3 Columnas --}}
    <table class="q-info-table">
        <tr>
            {{-- Columna 1 --}}
            <td width="35%">
                <span class="label-bold" style="margin-bottom: 5px;">Servicio :</span> <strong><u
                        style="text-decoration-thickness: 2px; text-underline-offset: 2px;">{{ strtoupper($servicio) }}</u></strong><br>
                <span class="label-bold" style="margin-bottom: 5px;">RUC :</span> {{ $ruc_empresa }}<br>
                <span class="label-bold" style="margin-bottom: 5px;">Empresa :</span> <span
                    class="text-blue">{{ $empresa_nombre }}</span><br>
                <span class="label-bold">Cotizado Por :</span> {{ $cotizado_por }}
            </td>

            {{-- Columna 2 (Alineada con Unidad) --}}
            <td width="40%" style="text-align: right;">
                <span class="label-bold" style="width: 140px;">N° de Solicitud:</span> <span class="text-blue"
                    style="border: 1px solid #000; padding: 3px 10px; min-width: 100px; display: inline-block; text-align: left;">{{ $n_solicitud }}</span><br>
                <span class="label-bold" style="width: 140px;">Cliente:</span> <span class="text-blue"
                    style="border: 1px solid #000; padding: 3px 10px; width: 100px; display: inline-block; text-align: left;">{{ $cliente }}</span><br>
                <span class="label-bold" style="width: 140px;">Jefe de Energía y SCI:</span> <span
                    style="border: 1px solid #000; padding: 3px 10px; width: 100px; display: inline-block; text-align: left;">{{ $jefe_energia }}</span><br>
                <span class="label-bold" style="width: 140px;">Fecha de cotización:</span> <span
                    style="border: 1px solid #000; padding: 3px 10px; width: 100px; display: inline-block; text-align: left;">{{ $fecha_cotizacion }}</span>
            </td>

            {{-- Columna 3 (Alineada con Subtotal) --}}
            <td width="25%">
                <span class="label-bold" style="width: 80px;">Categoria:</span> <span
                    style="border: 1px solid #000; padding: 3px 10px; font-weight: bold; width: 120px; display: inline-block;">{{ $categoria }}</span><br>
                <span class="label-bold" style="width: 80px;">CECO:</span> <span
                    style="border: 1px solid #000; padding: 3px 10px; width: 120px; display: inline-block;">{{ $ceco }}</span><br>
                <span class="label-bold" style="width: 80px;">Fecha ejec:</span> <span
                    style="border: 1px solid #000; padding: 3px 10px; background-color: #ffcccc; width: 120px; display: inline-block;">{{ $fecha_ejecucion }}</span><br>
                <span class="label-bold" style="width: 80px;">Total:</span>
                <span
                    style="border: 1px solid #000; padding: 3px 10px; background-color: #ffff00; font-weight: bold; text-align: center; width: 120px; display: inline-block;">S/
                    {{ $total_general }}</span>

            </td>
        </tr>
    </table>

    {{-- Tabla de Detalles --}}
    <table class="q-table" @if (isset($isPdf)) style="table-layout: auto; width: 100%;" @endif>
        <thead>
            <tr>
                <th style="width: 3%;">Item</th>
                <th style="width: 7%;">Línea</th>
                <th style="width: 32%;">Descripción de línea del preciario</th>
                <th style="width: 20%;">Comentario</th>
                <th style="width: 10%;">Unidad</th>
                <th style="width: 5%;">Cant.</th>
                <th style="width: 8%;">P.u</th>
                <th style="width: 13%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                @if ($item['tipo'] == 'header')
                    <tr class="row-category">
                        <td class="center">{{ $item['numero'] }}</td>
                        <td colspan="7" style="padding-left: 10px;">{{ $item['nombre'] }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="center">{{ $item['line'] }}</td> <!-- Cambiado para mostrar el número de línea -->
                        <td class="center"><strong>{{ $item['linea'] }}</strong></td>
                        <td style="padding: 5px;">{{ $item['descripcion'] }}</td>
                        <td style="padding: 5px;">{{ $item['comentario'] }}</td>
                        <td class="center">{{ $item['unidad'] }}</td>
                        <td class="center">{{ $item['cantidad'] > 0 ? $item['cantidad'] : '' }}</td>
                        <td class="center" style="padding-right: 5px;">
                            {{ $item['pu'] > 0 ? 'S/ ' . number_format($item['pu'], 2) : '' }}
                        </td>
                        <td class="center" style="padding-right: 5px; font-weight: bold;">
                            S/ {{ number_format($item['subtotal'], 2) }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
