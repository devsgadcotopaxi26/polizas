<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Pólizas</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #024283; font-size: 18px; margin: 0; }
        .header p { font-size: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #024283; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Pólizas TGT-GADPC</h1>
        <p>Generado el: {{ date('d/m/Y H:i') }} | Generado por: {{ $generadoPor ?? 'Sistema' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Póliza</th>
                <th>Categoría</th>
                <th>Subtipo</th>
                <th>Valor Asegurado</th>
                <th>Fecha Inicio</th>
                <th>Fecha Vencimiento</th>
                <th>{{ isset($esGestorAmbiental) && $esGestorAmbiental ? 'Operador Ambiental' : 'Contratista' }}</th>
                <th>Aseguradora</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($polizas as $poliza)
                <tr>
                    <td>{{ $poliza->numero_poliza }}</td>
                    <td>{{ ucfirst($poliza->categoria_poliza) }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($poliza->subtipo_poliza)) }}</td>
                    <td>${{ number_format($poliza->valor_asegurado, 2) }}</td>
                    <td>{{ $poliza->fecha_inicio->format('d/m/Y') }}</td>
                    <td>{{ $poliza->fecha_vencimiento->format('d/m/Y') }}</td>
                    <td>
                        @if(isset($esGestorAmbiental) && $esGestorAmbiental)
                            {{ $poliza->operadorAmbiental ? $poliza->operadorAmbiental->nombre : 'N/A' }}
                        @else
                            {{ $poliza->contrato && $poliza->contrato->contratista ? $poliza->contrato->contratista->nombre_cont : 'N/A' }}
                        @endif
                    </td>
                    <td>{{ $poliza->aseguradora ? $poliza->aseguradora->nombre_empresa : 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $poliza->estado)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
