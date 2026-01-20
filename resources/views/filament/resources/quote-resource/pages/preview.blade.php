<style>
    :root {
        --primary-green: #28a745;
        --soft-green: #e9f5ec;
        --border-color: #dee2e6;
        --text-blue: #0056b3;
    }

    .quotation-container {
        font-family: 'Segoe UI', Arial, sans-serif;
        padding: 20px;
        color: #333;
        max-width: 1200px;
        margin: auto;
    }

    /* Encabezado */
    .q-title-section {
        background-color: var(--primary-green);
        color: white;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 4px 4px 0 0;
    }

    .q-title-section h1 {
        margin: 0;
        font-size: 1.2rem;
    }

    .q-info-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr;
        gap: 20px;
        padding: 15px;
        background: #fdfdfd;
        border: 1px solid var(--border-color);
    }

    .q-info-column p {
        margin: 5px 0;
        font-size: 0.85rem;
        display: flex;
        justify-content: space-between;
    }

    .box {
        border: 1px solid #ccc;
        min-width: 100px;
        padding: 2px 5px;
        display: inline-block;
        background: white;
    }

    .pink {
        background-color: #ffdce0;
    }

    .text-blue {
        color: var(--text-blue);
        font-weight: bold;
    }

    /* Total */
    .total-box {
        background-color: #fff200;
        border: 2px solid #000;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        font-weight: bold;
    }

    /* Tabla */
    .q-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 0.8rem;
    }

    .q-table th {
        background-color: var(--primary-green);
        color: white;
        padding: 8px;
        border: 1px solid #fff;
    }

    .q-table td {
        padding: 6px;
        border: 1px solid var(--border-color);
    }

    .row-category {
        background-color: var(--soft-green);
        text-transform: uppercase;
    }

    .footer-note {
        color: red;
        font-size: 0.75rem;
        margin-top: 5px;
        font-style: italic;
    }
</style>
<div class="quotation-container">
    <header class="q-header">
        <div class="q-title-section">
            <h1>COTIZACIÓN DE SERVICIOS</h1>
            <span class="q-number">N° {{ $numero_cotizacion }}</span>
        </div>

        <div class="q-info-grid">
            <div class="q-info-column">
                <p><strong>Servicio:</strong> <span>{{ $servicio }}</span></p>
                <p><strong>RUC:</strong> <span>{{ $ruc_empresa }}</span></p>
                <p><strong>Empresa:</strong> <span class="text-blue">{{ $empresa_nombre }}</span></p>
                <p><strong>Cotizado Por:</strong> <span>{{ $cotizado_por }}</span></p>
            </div>
            <div class="q-info-column">
                <p><strong>N° de Solicitud:</strong> <span class="box">{{ $n_solicitud }}</span></p>
                <p><strong>Cliente:</strong> <span class="text-blue">{{ $cliente }}</span></p>
                <p><strong>Jefe de Energía y SCI:</strong> <span class="box">{{ $jefe_energia }}</span></p>
                <p><strong>Fecha de cotización:</strong> <span class="box pink">{{ $fecha_cotizacion }}</span></p>
            </div>
            <div class="q-info-column">
                <p><strong>Categoría:</strong> <span class="box">{{ $categoria }}</span></p>
                <p><strong>CECO:</strong> <span class="box">{{ $ceco }}</span></p>
                <p><strong>Fecha ejec:</strong> <span class="box pink">{{ $fecha_ejecucion }}</span></p>
                <div class="total-box">
                    <strong>Total:</strong>
                    <div>
                        <span class="currency">S/</span>
                        <span class="amount">{{ $total_general }}</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

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
            @forelse ($items as $item)
                @if ($item['tipo'] == 'header')
                    <tr class="row-category">
                        <td>{{ $item['index'] }}</td>
                        <td colspan="7"><strong>{{ $item['descripcion'] }}</strong></td>
                    </tr>
                @else
                    <tr>
                        <td style="text-align: center;">{{ $item['index'] }}</td>
                        <td style="text-align: center;">{{ $item['linea'] }}</td>
                        <td>{{ $item['descripcion'] }}</td>
                        <td>{{ $item['comentario'] }}</td>
                        <td style="text-align: center;">{{ $item['unidad'] }}</td>
                        <td style="text-align: center;">{{ number_format($item['cantidad'], 0) }}</td>
                        <td style="text-align: right;">S/ {{ number_format($item['pu'], 2) }}</td>
                        <td style="text-align: right; font-weight: bold;">S/ {{ number_format($item['subtotal'], 2) }}
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No hay items registrados en esta cotización.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        * Esta es una previsualización del documento oficial.
    </div>
</div>
