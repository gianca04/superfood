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
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 500;
            color: #000;
            display: inline-block;
            min-width: 120px;
            text-align: center;
            background-color: #fff;
        }

        .conformity-note {
            font-size: 10px;
            margin: 10px 0 0 0;
            line-height: 1.3;
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
        }

        .assets-table td {
            padding: 2px 5px;
            border: 1px solid #00a99d;
            font-size: 10px;
            height: 18px;
        }

        .observations-box {
            width: 100%;
            min-height: 100px;
            border: 1px solid #00a99d;
            padding: 5px;
            font-size: 10px;
            line-height: 1.4;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signatures-table>tbody>tr>td {
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-header {
            background-color: #00a99d;
            color: white;
            text-align: center;
            padding: 6px 4px;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .signature-area {
            /* 1. Definimos el tamaño CUADRADO (mismo ancho y alto) */
            width: 100px;
            height: 100px;

            /* 2. Centramos el cuadro dentro de la celda de la tabla */
            margin: 0 auto;

            /* 3. Centramos la imagen dentro del cuadro */
            display: flex;
            justify-content: center;
            align-items: center;

            /* Opcional: Borde suave para guiar la vista (puedes quitarlo si prefieres transparente) */
            border: 1px dashed #ccc;
            background-color: #fff;
            /* Asegura fondo blanco */
            overflow: hidden;
        }

        .signature-area img {
            /* 4. Hacemos que la imagen nunca exceda el cuadro */
            max-width: 100%;
            max-height: 100%;

            /* 5. LA CLAVE: 'contain' ajusta la imagen para que se vea ENTERA dentro del cuadrado sin deformarse */
            object-fit: contain;
        }

        .signature-field-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-field-table td {
            padding: 4px 0;
            font-size: 12px;
        }

        .signature-field-table .label {
            font-weight: bold;
            width: 70px;
        }

        .signature-field-table .value {
            border-bottom: 2px solid #00a99d;
        }

        /* ===== Botones de Acción ===== */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: #eee;
            border-radius: 8px;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-pdf {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-excel {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-print {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        /* ===== CORRECCIÓN DE IMPRESIÓN ===== */
        @media print {

            html,
            body {
                width: 100%;
                height: 99.5% !important;
                /* Evita que el navegador pida una 2da hoja */
                margin: 0 !important;
                padding: 0 !important;
                background-color: white !important;
                overflow: hidden !important;
                /* Mata el scroll y la hoja extra */
            }

            .page {
                width: 100% !important;
                max-width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 10mm 15mm !important;
                /* Márgenes internos de la hoja */
                border: none !important;
                box-shadow: none !important;
                display: block !important;
                overflow: hidden !important;
            }

            .action-buttons,
            script,
            .swal2-container {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 0;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body>


    <!-- ===== PEVIEW BOTONES DE ACCIÓN ===== -->
    <div class="action-buttons">
        <button class="btn-back" onclick="cerrarPestana()" title="Cerrar pestaña">
            <span>⬅️</span> Volver
        </button>
        <button class="btn-pdf" onclick="descargarPDF()" title="Descargar como PDF">
            <span>📄</span> Descargar PDF
        </button>
        <button class="btn-excel" onclick="descargarExcel()" title="Descargar como Excel">
            <span>📊</span> Descargar Excel
        </button>
        <button class="btn-print" onclick="imprimirDocumento()" title="Imprimir documento">
            <span>🖨️</span> Imprimir
        </button>
    </div>

    <div class="page">

        <!-- ===== ENCABEZADO ===== -->
        <table class="header-table">
            <tr>
                <td style="width: 200px;">
                    @php
                        $logoPath = public_path('images/Logo2.png');
                        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
                        $logoMime = 'image/png';
                    @endphp
                    @if ($logoData)
                        <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Logo2"
                            style="height: 80px;">
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
                            <span class="label">A3) N° de Orden de trabajo:</span><br>
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
                        <td style="flex-grow: 1;">
                            <span class="date-box">
                                {{ !empty($fecha_inicio) ? $fecha_inicio : 'N/A' }}
                            </span>
                        </td>
                        <td class="form-label" style="padding-left: 30px;">A8) Fecha de fin:</td>
                        <td style="flex-grow: 1;"><span class="date-box">{{ $fecha_fin ?? 'N/A' }}</span></td>
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
                                <td class="asset-name">{{ $asset['name'] }} ({{ $asset['selected'] ? 'X' : '  ' }})
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

                <!-- Área de Firmas -->
                <table class="signatures-table">
                    <tr>
                        <!-- Firma Cliente -->
                        <td style="width: 50%; padding-right: 20px;">
                            <div class="signature-header">CLIENTE</div>

                            <div class="signature-area">
                                @if ($firma_cliente)
                                    <img src="{{ $firma_cliente }}" alt="Firma Cliente">
                                @endif
                            </div>

                            <div style="text-align: center; font-size: 10px; font-weight: bold; margin-bottom: 8px;">
                                FIRMA</div>

                            <table class="signature-field-table">
                                <tr>
                                    <td class="label">NOMBRE:</td>
                                    <td class="value">{{ $cliente_nombre }}</td>
                                </tr>
                                <tr>
                                    <td class="label">{{ $cliente_tipo_doc }}:</td>
                                    <td class="value">{{ $cliente_documento }}</td>
                                </tr>
                            </table>
                        </td>

                        <!-- Firma SAT Industriales -->
                        <td style="width: 50%; padding-left: 10px;">
                            <div class="signature-header">SAT INDUSTRIALES S.A.C.</div>

                            <div class="signature-area">
                                @if ($firma_empleado)
                                    <img src="{{ $firma_empleado }}" alt="Firma Empleado">
                                @endif
                            </div>

                            <div style="text-align: center; font-size: 10px; font-weight: bold; margin-bottom: 8px;">
                                FIRMA</div>

                            <table class="signature-field-table">
                                <tr>
                                    <td class="label">NOMBRE:</td>
                                    <td class="value">{{ $empleado_nombre }}</td>
                                </tr>
                                <tr>
                                    <td class="label">{{ $empleado_tipo_doc }}:</td>
                                    <td class="value">{{ $empleado_documento }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    @if (isset($isPreview) && $isPreview === true)
        <script>
            function descargarPDF() {
                Swal.fire({
                    title: 'Generando PDF...',
                    text: 'Estamos preparando tu documento, por favor espera.',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();

                        // 1. Iniciamos la apertura del PDF
                        window.open('{{ route('actas.pdf', $id ?? 0) }}', '_blank');

                        // 2. Esperamos un poco más para mostrar el éxito
                        // Subimos a 3000ms (3 segundos) para que se note el proceso
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Documento Generado!',
                                text: 'Si no se abrió automáticamente, revisa tus ventanas emergentes.',
                                timer: 2500, // El mensaje de éxito ahora dura más
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        }, 3000);
                    }
                });
            }
            const Toast = Swal.mixin({
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });

            function descargarExcel() {
                Swal.fire({
                    title: 'Preparando Excel',
                    html: 'Estamos organizando tus datos...',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    willClose: () => {
                        console.log("Iniciando descarga de Excel...");
                        window.open('{{ route('actas.excel', parameters: $id ?? 0) }}', '_blank');
                    }

                })
            }

            function imprimirDocumento() {
                Swal.fire({
                    title: '¿Preparar impresión?',
                    text: "Se abrirá el diálogo de impresión del sistema.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, imprimir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.print();
                    }
                });
            }

            function cerrarPestana() {
                window.close();
                setTimeout(function() {
                    window.history.back();
                }, 300);
            }
        </script>
    @else
        <script>
            // Funciones para los botones de acción
            function descargarPDF() {
                window.location.href = '{{ route('actas.pdf', $id ?? 0) }}';
            }

            function descargarExcel() {
                window.location.href = '{{ route('actas.excel', $id ?? 0) }}';
            }

            function imprimirDocumento() {
                window.print();
            }

            function cerrarPestana() {
                window.close();
            }
        </script>
    @endif
</body>

</html>
