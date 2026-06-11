<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Oficio N° {{ $numeroOficio }}</title>
    <style>
        @page {
            margin: 30px 40px 80px 40px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
        }

        /* HEADER */
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .header td {
            vertical-align: top;
            border: none;
            padding: 0;
        }

        .header-logo-left {
            width: 80px;
            height: 80px;
        }

        .header-logo-right {
            text-align: right;
        }

        .header-logo-right h1 {
            margin: 0;
            font-size: 16pt;
            font-weight: normal;
        }

        .header-logo-right h2 {
            margin: 0;
            font-size: 18pt;
            font-weight: 900;
            letter-spacing: 2px;
        }

        .header-logo-right p {
            margin: 0;
            font-size: 8pt;
            color: #555;
            font-style: italic;
        }

        /* METADATA OFICIO */
        .oficio-meta {
            text-align: right;
            margin-bottom: 30px;
        }

        .oficio-meta p {
            margin: 2px 0;
        }

        /* DESTINATARIO */
        .destinatario {
            margin-bottom: 10px;
        }

        .destinatario p {
            margin: 0;
        }

        .destinatario strong {
            font-size: 11pt;
        }

        /* CUERPO */
        .texto-principal {
            text-align: justify;
            margin-bottom: 20px;
        }

        .objeto-contrato {
            margin-bottom: 20px;
            display: flex;
            /* dompdf handles inline blocks well */
        }

        .objeto-contrato .label {
            font-weight: bold;
            display: inline-block;
            vertical-align: top;
            width: 200px;
        }

        .objeto-contrato .texto {
            display: inline-block;
            vertical-align: top;
            width: 440px;
            text-transform: uppercase;
        }

        /* TABLA PRINCIPAL */
        table.datos-poliza {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            text-align: center;
        }

        table.datos-poliza th,
        table.datos-poliza td {
            border: 1px solid #000;
            padding: 8px 5px;
            vertical-align: middle;
        }

        table.datos-poliza th {
            font-size: 9pt;
            background-color: #fff;
        }

        table.datos-poliza td {
            font-size: 9pt;
            text-transform: uppercase;
        }

        /* PARRAFO FINAL */
        .parrafo-final {
            text-align: justify;
            margin-bottom: 20px;
        }

        /* PIE DE FIRMAS */
        .firmas {
            width: 100%;
            margin-top: 10px;
        }

        .firmas table {
            width: 100%;
            text-align: center;
            border: none;
        }

        .firmas td {
            border: none;
            width: 33%;
            vertical-align: bottom;
        }

        .firma-sello {
            text-align: center;
            position: relative;
            top: -20px;
        }

        .firma-sello img {
            width: 120px;
            height: 120px;
            object-fit: contain;
        }

        .nombre-firma {
            border-top: 1px solid #fff;
            /* Invisible line just for spacing if we don't draw it */
        }

        /* FOOTER INFO */
        .footer-info {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            width: 100%;
            border-top: 2px solid #000;
            padding-top: 5px;
            text-align: center;
            font-size: 8pt;
        }

        .footer-info strong {
            color: #000;
        }
    </style>
</head>

<body>

    <!-- CABECERA -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 20%">
                    <div class="header-logo-left text-center">
                        @php
                            $escudoPath = public_path('images/escudo.jpg');
                        @endphp
                        @if(file_exists($escudoPath))
                            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($escudoPath)) }}"
                                alt="Escudo" style="max-height: 80px; width: auto;">
                        @else
                            <div style="border: 1px dashed #ccc; padding: 10px; font-size: 8pt; text-align: center;">Falta
                                escudo.jpg<br>en public/images/</div>
                        @endif
                    </div>
                </td>
                <td style="width: 80%; text-align: right; vertical-align: middle;">
                    @php
                        $logoPath = public_path('images/logo.svg');
                    @endphp
                    @if(file_exists($logoPath))
                        <img src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents($logoPath)) }}"
                            alt="Logo Prefectura Cotopaxi" style="max-height: 75px; width: auto; max-width: 100%;">
                    @else
                        <div style="border: 1px dashed #ccc; padding: 10px; font-size: 8pt; text-align: center;">
                            Falta logo.svg<br>en public/images/
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- METADATOS OFICIO -->
    <div class="oficio-meta">
        <p><strong>{{ $numeroOficio }}</strong></p>
        <p><strong>Asunto:</strong> Renovación de Póliza Nº {{ $poliza->numero_poliza }}</p>
        <p>Latacunga, {{ \Carbon\Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') }}</p>
    </div>

    <!-- DESTINATARIO -->
    <div class="destinatario">
        <p>Atención</p>
        <p><strong>{{ strtoupper($poliza->sucursal->aseguradora->nombre_empresa ?? 'N/A') }}</strong></p>
        <p>{{ $poliza->sucursal->ciudad->nombre ?? 'Ciudad no registrada' }}. -</p>
        <br>
        <p>De mi consideración:</p>
    </div>

    <!-- CUERPO TEXTO -->
    <div class="texto-principal">
        <p>
            Informo a ustedes que las pólizas número {{ $poliza->numero_poliza }} emitidas por su representada está por
            <strong>VENCER</strong> el
            {{ \Carbon\Carbon::parse($poliza->fecha_vencimiento)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') }},
            hago referencia al Contrato {{ $poliza->contrato->numero_contrato }} en su Cláusula Octava. -
            <strong>Garantías 8.2</strong> copio textual <em>"Las garantías entregadas se devolverán de acuerdo a lo
                establecido en el art. 77 .... Entre tanto deberán mantenerse vigentes, lo que será vigilado y exigido
                por el contratante."</em>
            <strong>8.3</strong> copio textual <em>"Ejecución de las garantías: Las garantías contractuales podrán ser
                ejecutadas por los siguientes casos. <strong>8.3.1 fiel cumplimiento de contrato. - b)</strong> si el
                contratista no la renovare cinco (5) días antes de su vencimiento."</em>
        </p>
    </div>

    <!-- OBJETO -->
    <div style="margin-bottom: 15px;">
        <table style="width: 100%; border: none; font-size: 8.5pt;">
            <tr>
                <td style="width: 30%; border: none; vertical-align: top; font-weight: bold;">OBJETO DEL CONTRATO:</td>
                <td style="width: 70%; border: none; vertical-align: top; text-transform: uppercase; text-align: justify;">
                    "{{ $poliza->contrato->objeto_contratacion ? strtoupper($poliza->contrato->objeto_contratacion) : 'NO REGISTRADO' }}"
                </td>
            </tr>
        </table>
    </div>

    <!-- TABLA DATOS -->
    <table class="datos-poliza">
        <thead>
            <tr>
                <th>AFIANZADO<br>/CONTRATISTA</th>
                <th>PÓLIZA<br>NÚMERO</th>
                <th>RAMO</th>
                <th>VIGENCIA<br>HASTA<br>LAS 24:00<br>DE</th>
                <th>VALOR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $poliza->contrato->contratista->nombre_cont ?? 'N/A' }}</td>
                <td>{{ $poliza->numero_poliza }}</td>
                <td>
                    @php
                        $ramos = [
                            'fiel_cumplimiento_ambiental' => 'FIEL CUMPLIMIENTO AMBIENTAL',
                            'fiel_cumplimiento' => 'FIEL CUMPLIMIENTO DE CONTRATO',
                            'buen_uso' => 'BUEN USO DE ANTICIPO'
                        ];
                        echo $ramos[$poliza->subtipo_poliza] ?? strtoupper(str_replace('_', ' ', $poliza->subtipo_poliza));
                    @endphp
                </td>
                <td>{{ \Carbon\Carbon::parse($poliza->fecha_vencimiento)->format('d/m/Y') }}</td>
                <td>$ {{ number_format($poliza->valor_asegurado, 2, '.', ',') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- PÁRRAFO FINAL -->
    <div class="parrafo-final">
        <p>
            Agradezco a ustedes una vez realizada la RENOVACIÓN se remita la Póliza de Seguro de Fianzas Público
            legalizado al correo
            <a href="mailto:mariela.quingaluisa@cotopaxi.gob.ec"
                style="color:#000; text-decoration: underline;">mariela.quingaluisa@cotopaxi.gob.ec</a> con copia a
            <a href="mailto:mentor.cordova@cotopaxi.gob.ec"
                style="color:#000; text-decoration: underline;">mentor.cordova@cotopaxi.gob.ec</a>;
            <a href="mailto:paulina.lopez@cotopaxi.gob.ec"
                style="color:#000; text-decoration: underline;">paulina.lopez@cotopaxi.gob.ec</a>.
        </p>
    </div>

    <div style="margin-bottom: 20px;">
        <p>Atentamente,</p>
    </div>

    <!-- FIRMAS -->
    <div class="firmas">
        <table style="width: 100%">
            <tr>
                <!-- FIRMA 1 -->
                <td style="width: 33%; text-align: left;">
                    <div class="nombre-firma">
                        <p style="margin:0; font-weight:normal;">Tlga. Mariela Quingaluisa</p>
                        <p style="margin:0; font-weight:bold; font-size:10pt;">GESTOR DE TESORERIA</p>
                    </div>
                </td>

                <!-- SELLO CENTRAL -->
                <td style="width: 34%; text-align: center;">
                    <div class="firma-sello">
                        @php
                            $selloPath = public_path('images/sello.jpg');
                        @endphp
                        @if(file_exists($selloPath))
                            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($selloPath)) }}"
                                alt="Sello Provincial">
                        @else
                            <div
                                style="border: 1px dashed #ccc; width: 120px; height: 120px; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #999;">
                                Falta sello.jpg<br>en public/images/
                            </div>
                        @endif
                    </div>
                </td>

                <!-- FIRMA 2 -->
                <td style="width: 33%; text-align: center;">
                    <div class="nombre-firma">
                        <p style="margin:0; font-weight:normal;">Lic. Mentor Córdova Naranjo</p>
                        <p style="margin:0; font-weight:bold; font-size:10pt;">TESORERO</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer-info">
        <strong>Dir:</strong> Calle Tarqui N° 507 y Quito &bull;
        <strong>Telf:</strong> (03) 2800 416 - 2800 418 &bull;
        <strong>Telefax:</strong> 2800 411<br>
        <strong>E-mail:</strong> info@cotopaxi.gob.ec &bull; www.cotopaxi.gob.ec &bull; Cotopaxi - Ecuador
    </div>

</body>

</html>