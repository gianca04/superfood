<style>
    @if (!isset($isPdf))
        @page {
            size: A4 portrait;
            margin: 0.5cm;
        }

        body {
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-family: Calibri, Arial, sans-serif;
        }

        .quotation-container {
            background-color: white;
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 12mm 12mm 12mm;
            margin: 30px auto;
            font-family: Calibri, Arial, sans-serif;
            color: #333;
            box-sizing: border-box;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            .quotation-container {
                margin: 0 !important;
                padding: 0.5cm !important;
                width: 100% !important;
                min-height: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
        }
    @else
        body {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: Calibri, Arial, sans-serif !important;
        }

        .quotation-container {
            background-color: white !important;
            width: 100% !important;
            min-height: unset !important;
            margin: 0 !important;
            padding: 0.5cm !important;
            box-shadow: none !important;
            border: none !important;
            font-family: Calibri, Arial, sans-serif !important;
            color: #333;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .action-bar,
        .no-print {
            display: none !important;
        }
    @endif

    .action-bar {
        background: #333;
        padding: 18px 0 18px 0;
        display: flex;
        justify-content: center;
        gap: 28px;
        position: sticky;
        top: 0;
        z-index: 1000;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
    }

    .btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 11px 26px;
        border-radius: 6px;
        text-decoration: none;
        font-family: Calibri, Arial, sans-serif;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        border: none;
        transition: background 0.18s, box-shadow 0.18s;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        outline: none;
    }

    .btn-back {
        background: #6c757d;
        color: white;
    }

    .btn-back:hover,
    .btn-back:focus {
        background: #565e64;
    }

    .btn-pdf {
        background: #d9534f;
        color: white;
    }

    .btn-pdf:hover,
    .btn-pdf:focus {
        background: #b52b27;
    }

    .btn-print {
        background: #0275d8;
        color: white;
    }

    .btn-print:hover,
    .btn-print:focus {
        background: #025aa5;
    }

    .btn-excel {
        background: #28a745;
        color: white;
    }

    .btn-excel:hover,
    .btn-excel:focus {
        background: #1e7e34;
    }

    .btn svg {
        width: 18px;
        height: 18px;
        vertical-align: middle;
        fill: currentColor;
    }

    /* Contenedor de la hoja A4 */
    .quotation-container {
        background-color: white;
        width: 210mm;
        min-height: 297mm;
        padding: 15mm 12mm 12mm 12mm;
        margin: 30px auto;
        /* box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); */
        font-family: Calibri, Arial, sans-serif;
        color: #333;
        box-sizing: border-box;
        border: none !important;
    }

    /* Título */
    .q-title-section {
        background-color: #28a745 !important;
        border: 1px solid #d1d5db;
        font-family: Calibri, Arial, sans-serif;
    }

    .q-title-table {
        width: 100%;
        border-collapse: collapse;
    }

    .q-title-table td {
        color: white !important;
        padding: 8px 15px;
        font-weight: bold;
        font-family: Calibri, Arial, sans-serif;
    }

    /* Cuadrícula de Información (Contenido más pequeño) */
    .q-info-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: -1px;
        table-layout: fixed;
        font-family: Calibri, Arial, sans-serif;
    }

    .q-info-table td {
        border: 1px solid #d1d5db;
        padding: 6px 6px;
        font-size: 10px;
        vertical-align: top;
        line-height: 1.3;
        color: #444;
        font-family: Calibri, Arial, sans-serif;
    }

    .box {
        border: 1px solid #d1d5db;
        padding: 1px 5px;
        background: #f9fafb;
        min-width: 50px;
        display: inline-block;
        font-family: Calibri, Arial, sans-serif;
    }

    .pink {
        background-color: #ffdce0 !important;
    }

    /* TOTAL: Negrita, más grande y sin el cuadrado negro fuerte */
    .total-label {
        font-size: 13px;
        font-weight: bold;
        color: #000;
        font-family: Calibri, Arial, sans-serif;
    }

    .yellow {
        background-color: #fff200 !important;
        border: 1px solid #d1d5db;
        font-weight: bold;
        text-align: center;
        font-size: 15px;
        color: #000;
        padding: 4px;
        font-family: Calibri, Arial, sans-serif;
    }

    .text-blue {
        color: #0056b3 !important;
        font-weight: bold;
        font-family: Calibri, Arial, sans-serif;
    }

    /* TABLA DE ITEMS: Líneas Negras */
    .q-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        border: 1px solid #000;
        table-layout: fixed;
        font-family: Calibri, Arial, sans-serif;
    }

    .q-table th {
        background-color: #28a745 !important;
        color: white !important;
        border: 1px solid #000;
        padding: 8px 4px;
        font-size: 12px;
        font-family: Calibri, Arial, sans-serif;
    }

    .q-table td {
        border: 1px solid #000;
        padding: 8px 4px;
        font-size: 11px;
        word-wrap: break-word;
        line-height: 1.2;
        color: #000;
        font-family: Calibri, Arial, sans-serif;
    }

    /* Ajuste de anchos para que la tabla ocupe más espacio y no se desacomoden los textos */
    .q-table th:nth-child(1),
    .q-table td:nth-child(1) {
        width: 6%;
    }

    .q-table th:nth-child(2),
    .q-table td:nth-child(2) {
        width: 12%;
    }

    .q-table th:nth-child(3),
    .q-table td:nth-child(3) {
        width: 32%;
    }

    .q-table th:nth-child(4),
    .q-table td:nth-child(4) {
        width: 16%;
    }

    .q-table th:nth-child(5),
    .q-table td:nth-child(5) {
        width: 10%;
    }

    .q-table th:nth-child(6),
    .q-table td:nth-child(6) {
        width: 7%;
    }

    .q-table th:nth-child(7),
    .q-table td:nth-child(7) {
        width: 8%;
    }

    .q-table th:nth-child(8),
    .q-table td:nth-child(8) {
        width: 9%;
    }

    .row-category {
        background-color: #c6e0b4 !important;
        font-weight: bold;
        text-transform: uppercase;
        font-family: Calibri, Arial, sans-serif;
    }

    .empty-row td {
        height: 20px;
    }
</style>

@if (!isset($isPdf))
    <div class="action-bar no-print">
        <a href="javascript:window.close()" class="btn btn-back">
            <svg viewBox="0 0 20 20">
                <path
                    d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 111.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" />
            </svg>
            Volver
        </a>
        <button onclick="window.print()" class="btn btn-print">
            <svg viewBox="0 0 20 20">
                <path
                    d="M6 2a2 2 0 00-2 2v2h12V4a2 2 0 00-2-2H6zm10 6H4a2 2 0 00-2 2v5a2 2 0 002 2h1v2a1 1 0 001 1h8a1 1 0 001-1v-2h1a2 2 0 002-2v-5a2 2 0 00-2-2zm-3 9H7v-3h6v3z" />
            </svg>
            Imprimir
        </button>
        <a href="{{ route('quotes.pdf', $original_id) }}" class="btn btn-pdf">
            <svg viewBox="0 0 20 20">
                <path
                    d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm2 2h4v4H8V4zm-2 6h8v6H6v-6z" />
            </svg>
            Descargar PDF
        </a>
        <a href="{{ route('quotes.excel', $original_id) }}" class="btn btn-excel">
            <svg viewBox="0 0 20 20">
                <path
                    d="M17 3a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V5a2 2 0 012-2h14zm-4.293 7.707a1 1 0 00-1.414-1.414L10 10.586 8.707 9.293a1 1 0 00-1.414 1.414L8.586 12l-1.293 1.293a1 1 0 101.414 1.414L10 13.414l1.293 1.293a1 1 0 001.414-1.414L11.414 12l1.293-1.293z" />
            </svg>
            Descargar EXCEL
        </a>
    </div>
@endif

<div class="quotation-container">
    {{-- Header Principal --}}
    <div class="q-title-section">
        <table class="q-title-table">
            <tr>
                <td align="center" style="font-size: 18px; letter-spacing: 1px;">COTIZACIÓN DE SERVICIOS</td>
                <td width="22%" align="right" style="border-left: 1px solid #d1d5db;">
                    N° <span
                        style="background: #c6e0b4; color: black; padding: 3px 12px; border: 1px solid #d1d5db;">{{ $quote_id }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="q-info-table">
        <tr>
            <td width="36%">
                <strong>Servicio :</strong> <span>{{ $servicio }}</span><br>
                <strong>RUC :</strong> <span style="margin-left: 25px;">{{ $ruc_empresa }}</span><br>
                <strong>Empresa :</strong> <span class="text-blue">{{ $empresa_nombre }}</span><br>
                <strong>Cotizado Por :</strong> <span style="text-transform: uppercase;">{{ $cotizado_por }}</span>
            </td>
            <td width="34%">
                <strong>N° de Solicitud :</strong> <span class="box">{{ $n_solicitud }}</span><br>
                <strong>Cliente :</strong> <span class="text-blue">{{ $cliente }}</span><br>
                <strong>Jefe de Energía y SCI :</strong> <span class="box">{{ $jefe_energia }}</span><br>
                <strong>Fecha de cotización :</strong> <span class="box pink">{{ $fecha_cotizacion }}</span>
            </td>
            <td width="30%">
                <strong>Categoria :</strong> <span class="box">{{ $categoria }}</span><br>
                <strong>CECO :</strong> <span class="box">{{ $ceco }}</span><br>
                <strong>Fecha ejec :</strong> <span class="box pink">{{ $fecha_ejecucion }}</span><br>
                <table width="100%" style="margin-top: 10px; border-collapse: collapse;">
                    <tr>
                        <td style="border:none; padding:0;" class="total-label">TOTAL :</td>
                        <td class="yellow" width="75%">S/ {{ $total_general }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="q-table">
        <thead>
            <tr>
                <th width="5%">Item</th>
                <th width="10%">Línea</th>
                <th width="36%">Descripción de línea del preciario</th>
                <th width="15%">Comentario</th>
                <th width="12%">Unidad</th>
                <th width="6%">Cant.</th>
                <th width="8%">P.U</th>
                <th width="8%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                @if ($item['tipo'] == 'header')
                    <tr class="row-category">
                        <td align="center">{{ $item['numero'] }}</td>
                        <td colspan="7" style="padding-left: 15px;">{{ $item['nombre'] }}</td>
                    </tr>
                @else
                    <tr>
                        <td align="center"></td>
                        <td align="center" style="font-size: 9px; font-weight: bold;">{{ $item['linea'] }}</td>
                        <td style="font-size: 9.5px;">{{ $item['descripcion'] }}</td>
                        <td style="font-size: 9px;">{{ $item['comentario'] }}</td>
                        <td align="center" style="font-size: 8.5px;">{{ $item['unidad'] }}</td>
                        <td align="center" style="font-weight: bold;">
                            {{ $item['cantidad'] > 0 ? $item['cantidad'] : '' }}</td>
                        <td align="right" style="white-space: nowrap;">
                            {{ $item['pu'] > 0 ? 'S/ ' . number_format($item['pu'], 2) : '#N/D' }}
                        </td>
                        <td align="right" style="font-weight: bold; white-space: nowrap;">
                            S/ {{ number_format($item['subtotal'], 2) }}
                        </td>
                    </tr>
                @endif
            @endforeach
            {{-- Elimina la fila vacía extra --}}
            {{-- <tr class="empty-row">
                @for ($j = 0; $j < 8; $j++)
                    <td>&nbsp;</td>
                @endfor
            </tr> --}}
        </tbody>
    </table>
</div>
