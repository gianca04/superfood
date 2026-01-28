<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Trabajo - SAT Industriales S.A.C</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 195mm;
            /* Fixed mm width to force layout stability */
            margin: 0 auto;
            border-left: 1.5pt solid #00B0B9;
            border-right: 1.5pt solid #00B0B9;
            min-height: 275mm;
            padding: 5mm 8mm;
            box-sizing: border-box;
        }

        /* ==================== HEADER ==================== */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            table-layout: fixed;
            /* Ensures strict adherence to widths */
        }

        .logo-cell {
            width: 60%;
            vertical-align: top;
        }

        .logo-img {
            width: 150px;
            height: auto;
        }

        .contact-info {
            font-size: 7.2pt;
            color: #000;
            line-height: 1.2;
            margin-top: 2px;
        }

        .report-info-cell {
            width: 40%;
            vertical-align: top;
            text-align: right;
        }

        .report-number {
            font-size: 16pt;
            font-weight: bold;
            color: #E30613;
        }

        .report-title {
            font-size: 10pt;
            font-weight: bold;
            color: #000;
            margin-top: 2px;
        }

        /* Date Box */
        .date-section {
            margin-top: 5px;
            text-align: right;
        }

        .date-table {
            margin-left: auto;
            border-collapse: collapse;
        }

        .date-header {
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            padding: 2px 8px;
        }

        .date-row td {
            padding: 2px 5px;
            font-size: 8pt;
        }

        .date-label {
            text-align: right;
            padding-right: 5px;
        }

        .date-box {
            border: 1.5px solid #00B0B9;
            width: 70px;
            height: 16px;
            text-align: center;
            font-size: 8pt;
        }

        /* ==================== CYAN SEPARATOR ==================== */
        .cyan-line {
            border-bottom: 2px solid #00B0B9;
            margin: 8px 0 5px 0;
        }

        /* ==================== CLIENT INFO ==================== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            table-layout: fixed;
        }

        .info-table td {
            padding: 3px 0;
            font-size: 8pt;
            vertical-align: bottom;
        }

        .info-label {
            font-weight: normal;
            white-space: nowrap;
            width: 70px;
        }

        .info-value {
            border-bottom: 1px solid #000;
            padding-left: 5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* ==================== SECTIONS ==================== */
        .section-title {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 3px;
        }

        .cyan-underline {
            border-bottom: 1px solid #00B0B9;
            min-height: 14px;
            padding: 2px 3px;
            font-size: 8pt;
            margin-bottom: 0;
            word-wrap: break-word;
            white-space: normal;
        }

        /* ==================== DATA TABLES ==================== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
        }

        .data-table th {
            background-color: #E8F7F7;
            border: 1px solid #00B0B9;
            padding: 4px 5px;
            font-size: 8pt;
            font-weight: normal;
            text-align: center;
        }

        .data-table td {
            border: 1px solid #00B0B9;
            padding: 3px 5px;
            font-size: 8pt;
            min-height: 14px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: middle;
        }

        .col-name {
            width: 55%;
            text-align: left;
        }

        .col-unit {
            width: 22%;
            text-align: center;
        }

        .col-qty {
            width: 23%;
            text-align: center;
        }

        /* Total Row */
        .total-row td {
            font-weight: normal;
        }

        .total-label {
            text-align: right;
            padding-right: 10px;
            border-right: none !important;
        }

        .total-value {
            text-align: center;
        }

        .no-border {
            border: none !important;
            background: transparent !important;
        }

        /* ==================== RECOMMENDATIONS ==================== */
        .recommendations-section {
            margin-top: 10px;
        }

        .recommendations-title {
            font-size: 8pt;
            margin-bottom: 3px;
        }

        /* ==================== SIGNATURES ==================== */
        .signatures-section {
            margin-top: 20px;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signature-col {
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-header {
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            height: 30px;
        }

        .inner-signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inner-signature-table td {
            font-size: 8pt;
            padding: 10px 0 2px 0;
            vertical-align: bottom;
        }

        .inner-signature-table .sig-label {
            font-weight: bold;
            width: 55px;
            white-space: nowrap;
        }

        .inner-signature-table .sig-line {
            border-bottom: 1px solid #000;
        }

        /* ==================== PRINT STYLES ==================== */
        @page {
            margin: 10mm;
            size: A4 portrait;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if(file_exists(public_path('images/Logo2.png')))
                        @php
                            $logoPath = public_path('images/Logo2.png');
                            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                        @endphp
                        <img src="{{ $logoBase64 }}" alt="SAT Industriales S.A.C" class="logo-img">
                    @else
                        <div style="font-size: 14pt; font-weight: bold; color: #00B0B9;">SAT INDUSTRIALES S.A.C
                        </div>
                    @endif
                    <div class="contact-info">
                        <div>Km 9 de la expansión Av. José Aguilar Santisteban. (Pista nueva Curumuy - Fundo las
                            Mercedes)</div>
                        <div>Tel.: 934 451 894 &nbsp;&nbsp;&nbsp; Web: www.sat-industriales.pe</div>
                        <div>Email: operaciones@sat-industriales.pe</div>
                    </div>
                </td>
                <td class="report-info-cell">
                    <div class="report-number">N° {{ str_pad($reportId, 6, '0', STR_PAD_LEFT) }}</div>
                    <div class="report-title">REPORTE DE TRABAJO</div>
                    <div class="date-section">
                        <table class="date-table">
                            <tr>
                                <td class="date-header" colspan="2">FECHA {{ $reportDate }}</td>
                            </tr>
                            <tr class="date-row">
                                <td class="date-label">Inicio :</td>
                                <td class="date-box">{{ $startTime }}</td>
                            </tr>
                            <tr class="date-row">
                                <td class="date-label">Fin :</td>
                                <td class="date-box">{{ $endTime }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Cyan Separator -->
        <div class="cyan-line"></div>

        <!-- CLIENT INFO -->
        <table class="info-table">
            <tr>
                <td class="info-label">Razón Social :</td>
                <td class="info-value" style="width: 35%;">{{ $clientName }}</td>
                <td class="info-label" style="text-align: right; width: 70px;">R.U.C. :</td>
                <td class="info-value" style="width: 45%;">{{ $documentNumber }}</td>
            </tr>
        </table>
        <table class="info-table">
            <tr>
                <td class="info-label">Tienda :</td>
                <td class="info-value" style="width: 35%;">{{ $storeName }}</td>
                <td class="info-label" style="text-align: right; width: 70px;">Dirección :</td>
                <td class="info-value" style="width: 45%;">{{ $storeAddress }}</td>
            </tr>
        </table>

        <!-- TRABAJOS A REALIZAR -->
        <div class="section-title">Trabajos a Realizar:</div>
        @php
            $workToDoLines = $workToDo !== 'N/A' ? explode("\n", $workToDo) : [];
            $minLines = 2;
        @endphp
        @if (count($workToDoLines) > 0)
            @foreach ($workToDoLines as $line)
                <div class="cyan-underline">{{ $line }}</div>
            @endforeach
            @for ($i = count($workToDoLines); $i < $minLines; $i++)
                <div class="cyan-underline">&nbsp;</div>
            @endfor
        @else
            @for ($i = 0; $i < $minLines; $i++)
                <div class="cyan-underline">&nbsp;</div>
            @endfor
        @endif


        {{-- MATERIALES/HERRAMIENTAS TABLE --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-name">Materiales Consumidos</th>
                    <th class="col-unit">Unidad</th>
                    <th class="col-qty">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @if(count($consumedItems) > 0)
                    @foreach($consumedItems as $item)
                        <tr>
                            <td class="col-name">
                                {{ $item['description'] }}
                                @if(!empty($item['sat_line']))
                                    ({{ $item['sat_line'] }})
                                @endif
                            </td>
                            <td class="col-unit">{{ $item['unit'] }}</td>
                            <td class="col-qty">{{ $item['quantity'] }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" style="text-align: center;">No hay materiales consumidos registrados.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- PERSONAL TABLE -->
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-name">Personal que realizó el trabajo:</th>
                    <th class="col-unit">H.H</th>
                    <th class="col-qty">Cargo</th>
                </tr>
            </thead>
            <tbody>
                @if(count($personnel) > 0)
                    @foreach($personnel as $person)
                        <tr>
                            <td class="col-name">{{ $person['nombre'] }}</td>
                            <td class="col-unit">{{ $person['hh'] }}</td>
                            <td class="col-qty">{{ $person['cargo'] }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" style="text-align: center;">No hay personal registrado.</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="total-label">TOTAL H.H.</td>
                    <td class="total-value">{{ $totalHours }}</td>
                    <td class="no-border"></td>
                </tr>
            </tfoot>
        </table>

        <!-- CONCLUSIONES -->
        <div class="section-title">Conclusiones:</div>
        @php
            $conclusionsLines = $conclusions !== 'N/A' ? explode("\n", $conclusions) : [];
            $minLines = 2;
        @endphp
        @if (count($conclusionsLines) > 0)
            @foreach ($conclusionsLines as $line)
                <div class="cyan-underline">{{ $line }}</div>
            @endforeach
            @for ($i = count($conclusionsLines); $i < $minLines; $i++)
                <div class="cyan-underline">&nbsp;</div>
            @endfor
        @else
            @for ($i = 0; $i < $minLines; $i++)
                <div class="cyan-underline">&nbsp;</div>
            @endfor
        @endif

        <!-- RECOMENDACIONES -->
        <div class="recommendations-section">
            <div class="recommendations-title"><strong>Recomendaciones u Observaciones:</strong></div>
            @php
                $suggestionsLines = $suggestions !== 'N/A' ? explode("\n", $suggestions) : [];
                $minSuggestionLines = 2;
            @endphp
            @if (count($suggestionsLines) > 0)
                @foreach ($suggestionsLines as $line)
                    <div class="cyan-underline">{{ $line }}</div>
                @endforeach
                @for ($i = count($suggestionsLines); $i < $minSuggestionLines; $i++)
                    <div class="cyan-underline">&nbsp;</div>
                @endfor
            @else
                @for ($i = 0; $i < $minSuggestionLines; $i++)
                    <div class="cyan-underline">&nbsp;</div>
                @endfor
            @endif
        </div>

        {{--
        <!-- SIGNATURES -->
        <div class="signatures-section">
            <table class="signatures-table">
                <tr>
                    <td class="signature-col">
                        <div class="signature-header">CLIENTE</div>
                        <table class="inner-signature-table">
                            <tr>
                                <td class="sig-label">FIRMA :</td>
                                <td class="sig-line">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="sig-label">NOMBRE :</td>
                                <td class="sig-line">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="sig-label">DNI :</td>
                                <td class="sig-line">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                    <td class="signature-col">
                        <div class="signature-header">SAT INDUSTRIALES S.A.C.</div>
                        <table class="inner-signature-table">
                            <tr>
                                <td class="sig-label">FIRMA :</td>
                                <td class="sig-line">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="sig-label">NOMBRE :</td>
                                <td class="sig-line">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="sig-label">DNI :</td>
                                <td class="sig-line">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        --}}
</body>

</html>