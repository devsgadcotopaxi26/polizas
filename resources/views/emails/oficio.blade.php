<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #024283;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }

        .content {
            background: #f9f9f9;
            padding: 25px;
            border: 1px solid #ddd;
        }

        .body-text {
            white-space: pre-wrap;
        }

        .footer {
            background: #eee;
            padding: 15px;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 8px 8px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin:0;">Sistema de Pólizas – Prefectura de Cotopaxi</h2>
    </div>
    <div class="content">
        <p class="body-text">{{ $cuerpo }}</p>
    </div>
    <div class="footer">
        Este correo fue generado automáticamente por el Sistema de Gestión de Pólizas.<br>
        Póliza N.° {{ $poliza->numero_poliza }}
    </div>
</body>

</html>