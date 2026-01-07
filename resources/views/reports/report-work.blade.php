 <!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Trabajo - SAT Industriales S.A.C</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background-color: #fff;
            padding: 0;
        }

        /* Page Container */
        .page {
            width: 100%;
            min-height: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 15px 20px;
        }

        /* Header Section */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 8px;
        }

        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }

        .logo {
            margin-bottom: 5px;
        }

        .logo-img {
            width: 180px;
            height: auto;
        }

        .contact-info {
            font-size: 9px;
            color: #333;
            line-height: 1.4;
        }

        .report-number {
            font-size: 20px;
            font-weight: bold;
            color: #df0e0e;
        }

        .report-title {
            font-size: 12px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
            font-style: italic;
        }

        .date-section {
            text-align: left;
            margin-top: 5px;
        }

        .date-label-header {
            display: block;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            color: #000;
            margin-bottom: 3px;
        }

        .date-row {
            margin-bottom: 2px;
            font-size: 9px;
        }

        .date-row label {
            display: inline-block;
            min-width: 40px;
        }

        .date-value {
            display: inline-block;
            border: 1px solid #999;
            padding: 2px 5px;
            min-width: 80px;
        }

        /* Client Info Section */
        .client-info {
            margin-bottom: 8px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .info-field {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
        }

        .info-field label {
            font-size: 10px;
            font-weight: normal;
            color: #000;
        }

        .field-value {
            display: inline;
            border-bottom: 1px solid #333;
            padding: 2px 5px;
            font-size: 10px;
        }

        /* Work Sections */
        .work-section {
            margin-bottom: 8px;
            border: 1px solid #d1d1d1;
        }

        .section-header {
            background: #ffffff;
            padding: 4px 8px;
            border-bottom: 1px solid #d1d1d1;
        }

        .section-header span {
            font-size: 10px;
            font-weight: normal;
            color: #000;
        }

        .section-content {
            padding: 5px;
            min-height: 50px;
            font-size: 10px;
            line-height: 1.4;
            white-space: pre-wrap;
        }

        /* Tables */
        .materials-section,
        .personnel-section {
            margin-bottom: 8px;
        }

        .materials-table,
        .personnel-table {
            width: 100%;
            border-collapse: collapse;
        }

        .materials-table th,
        .personnel-table th {
            background: transparent;
            border: 1px solid #999;
            padding: 5px 8px;
            font-size: 10px;
            font-weight: normal;
            text-align: center;
            color: #000;
        }

        .materials-table td,
        .personnel-table td {
            border: 1px solid #999;
            padding: 8px;
            font-size: 10px;
            height: 22px;
        }

        .col-materials,
        .col-personal {
            width: 55%;
        }

        .col-unit,
        .col-hh {
            width: 22%;
        }

        .col-qty,
        .col-cargo {
            width: 23%;
        }

        .personnel-table tfoot td {
            border: 1px solid #999;
        }

        .total-label {
            text-align: right;
            padding-right: 15px;
            font-weight: normal;
        }

        .no-border {
            border: none !important;
            background: transparent;
        }

        /* Recommendations Section */
        .recommendations-section {
            margin-bottom: 15px;
        }

        .recommendations-section .section-header {
            background: transparent;
            border: none;
            padding: 4px 0;
        }

        .recommendations-content {
            border-bottom: 1px solid #333;
            min-height: 40px;
            padding: 5px;
            font-size: 10px;
            line-height: 1.4;
            white-space: pre-wrap;
        }

        /* Signatures Section */
        .signatures-section {
            display: table;
            width: 100%;
            padding: 10px 30px;
            margin-top: 20px;
        }

        .signature-column {
            display: table-cell;
            width: 45%;
        }

        .signature-title {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #000;
        }

        .signature-row {
            margin-bottom: 15px;
        }

        .signature-row label {
            font-size: 10px;
            font-weight: bold;
            color: #000;
        }

        .signature-line {
            display: inline-block;
            width: 150px;
            border-bottom: 1px solid #333;
            margin-left: 5px;
        }

        /* Print Styles */
        @page {
            margin: 10mm;
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- Header Section -->
        <header class="header">
            <div class="header-left">
                <div class="logo">
                    <img src="{{ public_path('images/logo_sat.png') }}" alt="SAT Industriales S.A.C Logo" class="logo-img">
                </div>
                <div class="contact-info">
                    <p>Calle uno Principal s/n Las Mercedes de Pedregal, Piura - Piura</p>
                    <p>Tel.: 934451894 | Web: www.sat-industriales.pe</p>
                    <p>Email: operaciones@sat-industriales.pe</p>
                </div>
            </div>
            <div class="header-right">
                <div class="report-number">N° {{ $reportId }}</div>
                <div class="report-title">REPORTE DE TRABAJO</div>
                <div class="date-section">
                    <span class="date-label-header">FECHA: {{ $reportDate }}</span>
                    <div class="date-row">
                        <label>Inicio:</label>
                        <span class="date-value">{{ $startTime }}</span>
                    </div>
                    <div class="date-row">
                        <label>Fin:</label>
                        <span class="date-value">{{ $endTime }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Client Info Section -->
        <section class="client-info">
            <div class="info-row">
                <div class="info-field">
                    <label>Razón Social:</label>
                    <span class="field-value">{{ $clientName }}</span>
                </div>
                <div class="info-field">
                    <label>R.U.C.:</label>
                    <span class="field-value">{{ $documentNumber }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-field">
                    <label>Tienda:</label>
                    <span class="field-value">{{ $storeName }}</span>
                </div>
                <div class="info-field">
                    <label>Dirección:</label>
                    <span class="field-value">{{ $storeAddress }}</span>
                </div>
            </div>
        </section>

        <!-- Trabajos a Realizar -->
        <section class="work-section">
            <div class="section-header">
                <span>Trabajos a Realizar:</span>
            </div>
            <div class="section-content">{{ $workToDo }}</div>
        </section>

        <!-- Trabajos Realizados -->
        <section class="work-section">
            <div class="section-header">
                <span>Trabajos Realizados:</span>
            </div>
            <div class="section-content">{{ $workDone }}</div>
        </section>

        <!-- Materiales/Herramientas Table -->
        <section class="materials-section">
            <table class="materials-table">
                <thead>
                    <tr>
                        <th class="col-materials">Materiales/Herramientas</th>
                        <th class="col-unit">Unidad</th>
                        <th class="col-qty">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($toolsAndMaterials as $item)
                    <tr>
                        <td>{{ $item['nombre'] }}</td>
                        <td>{{ $item['unidad'] }}</td>
                        <td>{{ $item['cantidad'] }}</td>
                    </tr>
                    @empty
                    {{-- Mostrar filas vacías si no hay datos --}}
                    @for($i = 0; $i < 5; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    @endfor
                    @endforelse
                    {{-- Agregar filas vacías adicionales si hay pocos items --}}
                    @if(count($toolsAndMaterials) > 0 && count($toolsAndMaterials) < 5)
                    @for($i = count($toolsAndMaterials); $i < 5; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    @endfor
                    @endif
                </tbody>
            </table>
        </section>

        <!-- Personal Table -->
        <section class="personnel-section">
            <table class="personnel-table">
                <thead>
                    <tr>
                        <th class="col-personal">Personal que realizó el trabajo:</th>
                        <th class="col-hh">H.H</th>
                        <th class="col-cargo">Cargo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personnel as $person)
                    <tr>
                        <td>{{ $person['nombre'] }}</td>
                        <td>{{ $person['hh'] }}</td>
                        <td>{{ $person['cargo'] }}</td>
                    </tr>
                    @empty
                    {{-- Mostrar filas vacías si no hay datos --}}
                    @for($i = 0; $i < 5; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    @endfor
                    @endforelse
                    {{-- Agregar filas vacías adicionales si hay pocos items --}}
                    @if(count($personnel) > 0 && count($personnel) < 5)
                    @for($i = count($personnel); $i < 5; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    @endfor
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td class="total-label">TOTAL H.H.</td>
                        <td>{{ $totalHours }}</td>
                        <td class="no-border"></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <!-- Recomendaciones -->
        <section class="recommendations-section">
            <div class="section-header">
                <span>Recomendaciones u Observaciones:</span>
            </div>
            <div class="recommendations-content">{{ $suggestions }}</div>
        </section>

        <!-- Signatures Section -->
        <section class="signatures-section">
            <div class="signature-column">
                <div class="signature-title">CLIENTE</div>
                <div class="signature-row">
                    <label>FIRMA:</label>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-row">
                    <label>NOMBRE:</label>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-row">
                    <label>DNI:</label>
                    <span class="signature-line"></span>
                </div>
            </div>
            <div class="signature-column">
                <div class="signature-title">SAT INDUSTRIALES S.A.C.</div>
                <div class="signature-row">
                    <label>FIRMA:</label>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-row">
                    <label>NOMBRE:</label>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-row">
                    <label>DNI:</label>
                    <span class="signature-line"></span>
                </div>
            </div>
        </section>
    </div>
</body>

</html>