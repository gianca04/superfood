<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atención de Suministros</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 2px solid #ccc;
        }

        .header img {
            height: 60px;
        }

        .content {
            padding: 20px;
        }

        .t able {
            25103063 width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f4f4f4;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            header
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('images/Logo2.png') }}" alt="Logo Empresa">
        @if ($clientLogo)
            <img src="{{ $clientLogo }}" alt="Logo Cliente" style="height: 60px;">
        @endif
    </div>

    <div class="content">
        <h1>Atención de Suministros</h1>
        <p><strong>Cliente:</strong> {{ $clientName }}</p>
        <p><strong>Fecha de Cotización:</strong> {{ $quoteDate }}</p>
        <p><strong>Fecha de Ejecución:</strong> {{ $executionDate }}</p>
        <p><strong>Estado:</strong> {{ $status }}</p>

        <table class="table">
            <thead>
                <tr>
                    <th>Línea SAT</th>
                    <th>Descripción del Ítem</th>
                    <th>Unidad</th>
                    <th>Solicitado</th>
                    <th>Entregado</th>
                    <th>Por Despachar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($details as $item)
                    <tr>
                        <td>{{ $item['sat_line'] ?? '-' }}</td>
                        <td>{{ $item['sat_description'] ?? '-' }}</td>
                        <td>{{ $item['unit_name'] ?? '-' }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $item['entregado'] ?? 0 }}</td>
                        <td>{{ max(0, $item['quantity'] - ($item['entregado'] ?? 0)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- NUEVO: Observaciones -->
        @if (!empty($observations))
            <div style="margin-top: 20px;">
                <h3>Observaciones:</h3>
                <p>{{ $observations }}</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Generado automáticamente por el sistema de gestión de almacén.</p>
    </div>
</body>

</html>
