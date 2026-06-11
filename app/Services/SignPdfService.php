<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;

class SignPdfService
{
    /**
     * Firma un PDF generado por DOMPDF
     * 
     * @param string $pdfContent El contenido del PDF crudo (salida de DOMPDF->output())
     * @param string $certificadoPath Ruta relativa en Storage al certificado .p12
     * @param string $password Contraseña del certificado
     * @param string $nombreFirmante Nombre del firmante para los metadatos
     * @param string $motivo Razón de la firma
     * @return string El contenido del PDF firmado (String binario)
     */
    public function signPdfString(
        string $pdfContent,
        string $certificadoPath,
        string $password,
        string $nombreFirmante,
        array $rolesFirmante = [],
        string $motivo = '',
        ?float $sigX = null,
        ?float $sigY = null,
        ?int $sigPage = null
    ): string {
        // 1. Obtener certificado
        if (!Storage::disk('local')->exists($certificadoPath)) {
            throw new \Exception('Certificado digital no encontrado en la ruta especificada.');
        }

        $p12CertPath = Storage::disk('local')->path($certificadoPath);

        // Extraer nombre del firmante desde el P12 usando openssl para metadatos (opcional pero util)
        $tempP12Path = storage_path('app/temp_cert_' . uniqid() . '.p12');
        copy($p12CertPath, $tempP12Path);

        $command = sprintf(
            'openssl pkcs12 -in %s -passin pass:%s -nodes -legacy 2>&1',
            escapeshellarg($tempP12Path),
            escapeshellarg($password)
        );

        $output = shell_exec($command);
        @unlink($tempP12Path);

        if ($output === null || strpos($output, 'Mac verify error') !== false || strpos($output, 'invalid password') !== false) {
            throw new \Exception('Contraseña del certificado incorrecta.');
        }

        preg_match('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $output, $certMatches);
        $cn = $nombreFirmante; // Fallback
        if (!empty($certMatches)) {
            $certInfo = openssl_x509_parse($certMatches[0]);
            $cn = $certInfo['subject']['CN'] ?? $nombreFirmante;
        }

        // 2. Aplicar Firma Criptográfica y Estampado Visual Secuencial usando Python (pyHanko)
        // Guardamos el PDF de entrada (que puede estar o no firmado ya) en temporal
        $tempInputFile = storage_path('app/temp_input_' . uniqid() . '.pdf');
        file_put_contents($tempInputFile, $pdfContent);

        $tempSignedFile = storage_path('app/temp_signed_' . uniqid() . '.pdf');
        $pythonScriptPath = base_path('sign_pyhanko.py');

        // Construir string de roles
        $rolesArgs = '';
        foreach ($rolesFirmante as $role) {
            $rolesArgs .= '--roles ' . escapeshellarg($role) . ' ';
        }
        $rolesArgs = trim($rolesArgs);

        // Leer la versión y nombre de la aplicación desde config
        $appVersion = config('app.firma_version', '1.0.0');
        $appName    = config('app.firma_app_name', 'SisPolizas-GADPC');
        $tsaUrl     = config('app.firma_tsa_url', '');

        $pythonCommand = sprintf(
            'python %s --input %s --output %s --p12 %s --password %s --name %s --reason %s %s %s %s --location %s --app-version %s --app-name %s --tsa-url %s %s 2>&1',
            escapeshellarg($pythonScriptPath),
            escapeshellarg($tempInputFile),
            escapeshellarg($tempSignedFile),
            escapeshellarg($p12CertPath),
            escapeshellarg($password),
            escapeshellarg($cn),
            escapeshellarg(""),
            $sigX !== null ? '--sig-x ' . escapeshellarg((string) $sigX) : '',
            $sigY !== null ? '--sig-y ' . escapeshellarg((string) $sigY) : '',
            $sigPage !== null ? '--sig-page ' . escapeshellarg((string) $sigPage) : '',
            escapeshellarg(""),
            escapeshellarg($appVersion),
            escapeshellarg($appName),
            escapeshellarg($tsaUrl),
            $rolesArgs
        );

        $pythonOutput = shell_exec($pythonCommand);

        // Log the raw output before any conversion for debugging
        \Illuminate\Support\Facades\Log::error('PyHanko Raw Output: ' . bin2hex(substr($pythonOutput, 0, 500)));
        \Illuminate\Support\Facades\Log::error('PyHanko Output (UTF-8 forced): ' . mb_convert_encoding($pythonOutput, 'UTF-8', 'ISO-8859-1'));

        // Clean output for exception message to avoid JSON encoding errors
        $cleanOutput = mb_convert_encoding($pythonOutput, 'UTF-8', 'UTF-8');
        if (empty($cleanOutput)) {
            $cleanOutput = utf8_encode($pythonOutput); // Fallback
        }


        if (!file_exists($tempSignedFile) || strpos($pythonOutput, 'ERROR') !== false || strpos($pythonOutput, 'SUCCESS') === false) {
            throw new \Exception('Fallo al firmar criptográficamente (Python). Revisa los logs de Laravel. Detalles: ' . substr($cleanOutput, 0, 200));
        }

        // 3. Salida
        $finalContent = file_get_contents($tempSignedFile);

        // Limpieza
        @unlink($tempInputFile);
        @unlink($tempSignedFile);

        return $finalContent;
    }
}
