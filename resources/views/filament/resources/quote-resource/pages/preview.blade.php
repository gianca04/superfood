<style>
    /* 1. Colores fijos (mPDF no soporta :root o var) */
    .quotation-container {
        font-family: Arial, sans-serif;
        padding: 10px;
        color: #333333;
        width: 100%;
    }

    /* 2. Encabezado Principal */
    .q-title-section {
        background-color: #28a745;
        color: #ffffff;
        width: 100%;
    }

    .q-title-table {
        width: 100%;
        border-collapse: collapse;
    }

    .q-title-table td {
        padding: 10px 20px;
        color: #ffffff;
        font-weight: bold;
    }

    /* 3. Grid de Información (Convertido a Tabla para PDF) */
    .q-info-table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fdfdfd;
        border: 1px solid #dee2e6;
    }

    .q-info-table td {
        width: 33.3%;
        /* Dividimos en 3 columnas iguales */
        padding: 10px;
        vertical-align: top;
        font-size: 0.85rem;
    }

    .q-info-table p {
        margin: 4px 0;
    }

    .box {
        border: 1px solid #cccccc;
        padding: 2px 5px;
        background-color: #ffffff;
        display: inline-block;
    }

    .pink {
        background-color: #ffdce0;
    }

    .text-blue {
        color: #0056b3;
        font-weight: bold;
    }

    /* 4. Caja de Total */
    .total-box {
        background-color: #fff200;
        border: 2px solid #000000;
        padding: 8px;
        margin-top: 10px;
        font-weight: bold;
        text-align: center;
    }

    /* 5. Tabla de Items */
    .q-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .q-table th {
        background-color: #28a745;
        color: #ffffff;
        padding: 8px;
        font-size: 0.8rem;
        border: 1px solid #ffffff;
    }

    .q-table td {
        padding: 6px;
        border: 1px solid #dee2e6;
        font-size: 0.75rem;
    }

    .row-category {
        background-color: #e9f5ec;
        text-transform: uppercase;
        font-weight: bold;
    }
</style>
<div class="quotation-container">
    {{-- Encabezado --}}
    <div class="q-title-section">
        <table class="q-title-table">
            <tr>
                <td align="left">
                    <h1>COTIZACIÓN DE SERVICIOS</h1>
                </td>
                <td align="right" style="font-size: 1.2rem;">N° {{ $numero_cotizacion }}</td>
            </tr>
        </table>
    </div>

    {{-- Info Grid (Layout de 3 columnas usando tabla) --}}
    <table class="q-info-table">
        <tr>
            {{-- Columna 1 --}}
            <td>
                <p><strong>Servicio:</strong> <span>{{ $servicio }}</span></p>
                <p><strong>RUC:</strong> <span>{{ $ruc_empresa }}</span></p>
                <p><strong>Empresa:</strong> <span class="text-blue">{{ $empresa_nombre }}</span></p>
                <p><strong>Cotizado Por:</strong> <span>{{ $cotizado_por }}</span></p>
            </td>
            {{-- Columna 2 --}}
            <td>
                <p><strong>N° de Solicitud:</strong> <span class="box">{{ $n_solicitud }}</span></p>
                <p><strong>Cliente:</strong> <span class="text-blue">{{ $cliente }}</span></p>
                <p><strong>Jefe de Energía y SCI:</strong> <span class="box">{{ $jefe_energia }}</span></p>
                <p><strong>Fecha de cotización:</strong> <span class="box pink">{{ $fecha_cotizacion }}</span></p>
            </td>
            {{-- Columna 3 --}}
            <td>
                <p><strong>Categoría:</strong> <span class="box">{{ $categoria }}</span></p>
                <p><strong>CECO:</strong> <span class="box">{{ $ceco }}</span></p>
                <p><strong>Fecha ejec:</strong> <span class="box pink">{{ $fecha_ejecucion }}</span></p>

                <div class="total-box">
                    <span>TOTAL: S/ {{ $total_general }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabla de Items --}}
    <table class="q-table">
        <thead>
            <tr>
                <th width="5%">Item</th>
                <th width="10%">Línea</th>
                <th width="40%">Descripción de línea del preciario</th>
                <th width="15%">Comentario</th>
                <th width="10%">Unidad</th>
                <th width="5%">Cant.</th>
                <th width="8%">P.U</th>
                <th width="7%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                @if ($item['tipo'] == 'header')
                    <tr class="row-category">
                        <td align="center">{{ $item['numero'] }}</td>
                        <td colspan="7" align="center">{{ $item['nombre'] }}</td>
                    </tr>
                @else
                    <tr>
                        <td></td>
                        <td align="center">{{ $item['linea'] }}</td>
                        <td>{{ $item['descripcion'] }}</td>
                        <td>{{ $item['comentario'] }}</td>
                        <td align="center">{{ $item['unidad'] }}</td>
                        <td align="center">{{ number_format($item['cantidad'], 0) }}</td>
                        <td align="right">{{ number_format($item['pu'], 2) }}</td>
                        <td align="right">{{ number_format($item['subtotal'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
