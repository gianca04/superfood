<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Acta de Conformidad - SAT Industriales S.A.C.</title>
    <style>
        /* ===== Reset y Fuentes ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            background-color: #f5f5f5;
            padding: 20px;
            color: #000;
            margin: 0;
        }

        /* ===== Contenedor Principal ===== */
        .page {
            width: 100%;
            max-width: 900px;
            background: white;
            padding: 10px 15px;
            border: 1px solid #00a99d;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* ===== Encabezado con Tabla ===== */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 5px;
        }

        .company-name {
            font-weight: bold;
            font-size: 14px;
            color: #00a99d;
        }

        .company-subtitle {
            font-size: 8px;
            color: #000;
        }

        .header-title {
            text-align: center;
        }

        .header-title h1 {
            font-size: 16px;
            color: #000;
            font-weight: normal;
            margin-bottom: 2px;
        }

        .header-title h2 {
            font-size: 12px;
            color: #000;
            font-weight: normal;
        }

        .header-number {
            text-align: right;
            color: #FF0000;
            font-size: 18px;
            font-weight: bold;
        }

        /* ===== Estilos de Sección ===== */
        .section {
            margin-bottom: 8px;
            border: 1px solid #00a99d;
        }

        .section-header {
            background-color: #00a99d;
            color: white;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 11px;
        }

        .section-content {
            padding: 8px;
            background: #fff;
        }

        /* ===== Tabla de Formulario ===== */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .form-table td {
            padding: 2px 5px;
            vertical-align: bottom;
        }

        .form-label {
            font-weight: bold;
            font-size: 10px;
            white-space: nowrap;
        }

        .form-value {
            border-bottom: 1px solid #00a99d;
            font-size: 10px;
        }

        /* ===== Tabla Compuesta OT/Descripción ===== */
        .composite-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border: 1px solid #00a99d;
        }

        .composite-table td {
            padding: 5px;
            vertical-align: top;
            font-size: 10px;
        }

        .composite-table .label {
            font-weight: bold;
        }

        .composite-table .left {
            width: 130px;
            border-right: 1px solid #00a99d;
        }

        /* ===== Fechas ===== */
        .dates-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .dates-table td {
            padding: 2px 5px;
            vertical-align: middle;
            font-size: 10px;
        }

        .date-box {
            border: 1px solid #00a99d;
            padding: 3px 8px;
        }

        /* ===== Nota de conformidad ===== */
        .conformity-note {
            font-size: 10px;
            margin: 10px 0 0 0;
            line-height: 1.3;
        }

        /* ===== Tabla de Activos ===== */
        .section-subtitle {
            font-size: 10px;
            margin-bottom: 8px;
        }

        .assets-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .assets-table th {
            color: #00a99d;
            font-style: italic;
            font-weight: bold;
            padding: 2px 5px;
            text-align: center;
            border: 1px solid #00a99d;
            font-size: 10px;
            background: transparent;
        }

        .assets-table td {
            padding: 2px 5px;
            border: 1px solid #00a99d;
            font-size: 10px;
            height: 18px;
        }

        .assets-table td.number {
            text-align: center;
            color: #00a99d;
            font-weight: bold;
            font-style: italic;
        }

        .assets-table td.asset-name {
            font-style: italic;
            font-weight: bold;
        }

        /* ===== Área de Observaciones ===== */
        .observations-area {
            margin-top: 5px;
        }

        .observations-label {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .observations-box {
            width: 100%;
            min-height: 100px;
            border: 1px solid #00a99d;
            padding: 5px;
            font-size: 10px;
            line-height: 1.4;
        }

        /* ===== Sección de Firmas ===== */
        .responsibility-text {
            font-size: 7px;
            text-align: justify;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .client-acceptance {
            font-size: 8px;
            text-transform: uppercase;
            margin-bottom: 15px;
            font-weight: bold;
            margin-top: 5px;
        }

        /* ===== Sección de Firmas AMPLIADA ===== */
        /* Busca y reemplaza esta sección en tu <style> */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
            /* Fuerza a que las columnas midan lo mismo */
        }

        .signatures-table td {
            width: 45%;
            /* Reducimos un poco el ancho para dejar aire */
            vertical-align: top;
            padding: 0 15px;
            /* Espacio interno a los lados */
        }

        .signature-header {
            background-color: #00a99d;
            color: white;
            text-align: center;
        }

        .signature-label-text {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }

        /* Tabla de datos del firmante */
        .signature-field-table {
            width: 100%;
            margin-top: 15px;
        }

        .signature-field-table td {
            padding: 12px 0;
            /* Más espacio entre filas de datos */
            vertical-align: middle;
        }

        .signature-field-table .label {
            font-weight: bold;
            width: 35%;
            /* Ajuste de ancho */
            /* Texto más legible */
        }

        .signature-field-table .value {
            border-bottom: 2px solid #00a99d;
            /* Línea más gruesa y visible */
            padding-left: 10px;
        }
    </style>
</head>

<body>
    <!-- ===== ENCABEZADO ===== -->
    <table class="header-table">
        <tr>
            <td style="width: 200px;">
                @if (isset($logo_base64) && $logo_base64)
                    <img src="{{ $logo_base64 }}" alt="Logo" style="height: 80px;">
                @endif
            </td>
            <td class="header-title">
                <h1>Acta de conformidad</h1>
                <h2>Gerencia de ingeniería y Mantenimiento</h2>
            </td>
            <td class="header-number" style="width: 150px;">
                Nº <span style="font-size: 22px;">{{ $numero }}</span>
            </td>
        </tr>
    </table>

    <!-- ===== SECCIÓN A: Información del trabajo realizado ===== -->
    <div class="section">
        <div class="section-header">SECCIÓN A: Información del trabajo realizado</div>
        <div class="section-content">
            <!-- Fila 1: Razón Social y R.U.C. -->
            <table class="form-table">
                <tr>
                    <td class="form-label" style="width: 100px;">A1) Razón Social:</td>
                    <td class="form-value">{{ $razon_social }}</td>
                    <td class="form-label" style="width: 50px;">R.U.C.:</td>
                    <td class="form-value" style="width: 120px;">{{ $ruc }}</td>
                </tr>
            </table>

            <!-- Fila 2: Tienda y Dirección -->
            <table class="form-table">
                <tr>
                    <td class="form-label" style="width: 70px;">A2) Tienda:</td>
                    <td class="form-value">{{ $tienda }}</td>
                    <td class="form-label" style="width: 70px;">Dirección:</td>
                    <td class="form-value">{{ $direccion }}</td>
                </tr>
            </table>

            <!-- Fila 3: N° de OT y Descripción del Servicio -->
            <table class="composite-table">
                <tr>
                    <td class="left">
                        <span class="label">A3) N° OT:</span><br>
                        {{ $work_order_number }}
                    </td>
                    <td>
                        <span class="label">A4) Descripción del Servicio:</span><br>
                        {{ $descripcion_servicio }}
                    </td>
                </tr>
                <tr>
                    <td class="left">
                        <span class="label">A5) N° de Solicitud:</span><br>
                        {{ $request_number ?? 'N/A' }}
                    </td>
                </tr>
            </table>

            <!-- Fila 4: Fechas -->
            <table class="dates-table">
                <tr>
                    <td class="form-label">A7) Fecha de inicio:</td>
                    <td><span class="date-box">{{ $fecha_inicio }}</span></td>
                    <td class="form-label" style="padding-left: 30px;">A8) Fecha de fin:</td>
                    <td><span class="date-box">{{ $fecha_fin }}</span></td>
                </tr>
            </table>

            <!-- Nota de Conformidad -->
            <div class="conformity-note">
                A9) En el presente documento, se consta la <strong>CONFORMIDAD</strong> de los servicios presentados
                por la empresa:
            </div>
        </div>
    </div>

    <!-- ===== SECCIÓN B: Disposición de los activos intervenidos ===== -->
    <div class="section">
        <div class="section-header">SECCIÓN B: Disposición de los activos intervenidos</div>
        <div class="section-content">
            <div class="section-subtitle">
                B1) En esta sección, <strong>el contratista o proveedor del servicio deberá enlistar todos los
                    activos intervenidos durante la actividad ejecutada.</strong>
            </div>

            <table class="assets-table">
                <thead>
                    <tr>
                        <th style="width: 30px;">N°</th>
                        <th style="width: 220px;">Tipo de Activos</th>
                        <th style="width: 80px;">Cantidad</th>
                        <th>Comentarios</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $index => $asset)
                        <tr>
                            <td class="number">{{ $index + 1 }}</td>
                            <td class="asset-name">{{ $asset['name'] }} ({{ $asset['selected'] ? 'X' : ' ' }})
                            </td>
                            <td style="text-align: center;">{{ $asset['quantity'] }}</td>
                            <td>{{ $asset['comments'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Área de Observaciones -->
            <div class="observations-area">
                <div class="observations-label">B2) Observaciones Generales del Mantenimiento Preventivo</div>
                <div class="observations-box">
                    {!! $observaciones !!}
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SECCIÓN C: Responsabilidad de la conformidad y firmas ===== -->
    <div class="section">
        <div class="section-header">SECCIÓN C: Responsabilidad de la conformidad y firmas</div>
        <div class="section-content">
            <div class="responsibility-text">
                Quien suscribe esta Acta, declara que se ha concluido el trabajo y no queda nada pendiente de
                culminar, en la medida que se ha dado cumplimiento a todos los requerimientos solicitados en la
                incidencia. La firma de esta Acta de Conformidad por quien figura como responsable, es el título que
                habilita la procedencia del pago en contraprestación al trabajo realizado, y por tanto genera
                responsabilidad directa en quien suscribe esta Acta debido al impacto económico que genera y a las
                disposiciones de seguridad que deben tomarse en cuenta, por lo que en caso de incumplimiento la
                empresa se encontrará en la facultad de tomar las medidas que correspondan. Sino hubiera conformidad
                con el trabajo que debía realizarse, el responsable debe <strong>Incluir observaciones.</strong>
            </div>

            <div class="client-acceptance">
                SEÑOR CLIENTE LA FIRMA DE ESTE DOCUMENTO DA POR ACEPTADO EL TRABAJO REALIZADO POR NUESTRO PERSONAL
            </div>


            <table class="signatures-table" border="0" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th
                            style="padding: 15px 10px; width: 50%; background-color: #00a99d; color: white; border: 5px solid white;">
                            <div class="signature-header">CLIENTE</div>
                        </th>
                        <th
                            style="padding: 15px 10px; width: 50%; background-color: #00a99d; color: white; border: 5px solid white;">
                            <div class="signature-header">SAT INDUSTRIALES S.A.C.</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center; padding: 10px; height: 100px; vertical-align: bottom;">
                            @if ($firma_cliente)
                                <img src="{{ $firma_cliente }}" alt="Firma Cliente"
                                    style="max-height: 80px; max-width: 100%;">
                            @else
                                <br><br><br>
                            @endif
                        </td>
                        <td style="text-align: center; padding: 10px; height: 100px; vertical-align: bottom;">
                            @if ($firma_empleado)
                                <img src="{{ $firma_empleado }}" alt="Firma Empleado"
                                    style="max-height: 80px; max-width: 100%;">
                            @else
                                <br><br><br>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 5px; text-align: center; font-weight: bold;">FIRMA</td>
                        <td style="padding: 5px; text-align: center; font-weight: bold;">FIRMA</td>
                    </tr>

                    <tr>
                        <td style="padding: 0;">
                            <table class="signature-field-table"
                                style="width: 100%; border-collapse: collapse; border: none;">
                                <tr>
                                    <td class="label" style="width: 30%; padding: 5px; font-weight: bold;">
                                        NOMBRE:
                                    </td>
                                    <td class="value" style="width: 70%; padding: 5px;">
                                        {{ $cliente_nombre }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 0;">
                            <table class="signature-field-table"
                                style="width: 100%; border-collapse: collapse; border: none;">
                                <tr>
                                    <td class="label" style="width: 30%; padding: 5px; font-weight: bold;">
                                        NOMBRE:
                                    </td>
                                    <td class="value" style="width: 70%; padding: 5px;">
                                        {{ $empleado_nombre }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0;">
                            <table class="signature-field-table"
                                style="width: 100%; border-collapse: collapse; border: none;">
                                <tr>
                                    <td class="label" style="width: 30%; padding: 5px; font-weight: bold;">
                                        {{ $cliente_tipo_doc ?? 'DOC' }}:
                                    </td>
                                    <td class="value" style="width: 70%; padding: 5px;">
                                        {{ $cliente_documento }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 0;">
                            <table class="signature-field-table"
                                style="width: 100%; border-collapse: collapse; border: none;">
                                <tr>
                                    <td class="label" style="width: 30%; padding: 5px; font-weight: bold;">
                                        {{ $empleado_tipo_doc ?? 'DOC' }}:
                                    </td>
                                    <td class="value" style="width: 70%; padding: 5px;">
                                        {{ $empleado_documento }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
