<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Configuracion;
use App\Models\Oficio;
use App\Models\Poliza;

class ConfiguracionController extends Controller
{
    /**
     * Muestra la pantalla de configuración para el Administrador.
     */
    public function index()
    {
        $anio = now()->year;

        $configMin = (int) Configuracion::getValor('siguiente_numero_oficio_' . $anio, 0);
        $maxDb = Oficio::where('anio', $anio)->max('numero') ?? 0;

        $siguienteCalculado = max($maxDb + 1, $configMin);

        // --- Pólizas ---
        $secuenciasPolizas = [];

        // 1. Ambiental
        $catAmbiental = Poliza::CATEGORIA_AMBIENTAL;
        $maxDbAmbiental = Poliza::withTrashed()->where('categoria_poliza', $catAmbiental)->max('codigo') ?? 0;
        $inicioDbAmbiental = (int) Configuracion::getValor('secuencia_inicio_poliza_' . $catAmbiental, Poliza::CODIGO_INICIO[$catAmbiental] ?? 0);
        $secuenciasPolizas[$catAmbiental] = [
            'configurado' => $inicioDbAmbiental,
            'ultimo' => $maxDbAmbiental,
            'siguiente' => max($maxDbAmbiental, $inicioDbAmbiental) + 1,
            'label' => 'Pólizas Ambientales',
        ];

        // 2. Obras y Proveedores (Comparten secuencia)
        $catCompartida = 'obras_proveedores';
        $maxDbCompartida = Poliza::withTrashed()
            ->whereIn('categoria_poliza', [Poliza::CATEGORIA_OBRAS, Poliza::CATEGORIA_PROVEEDORES])
            ->max('codigo') ?? 0;

        $inicioDbCompartida = (int) Configuracion::getValor('secuencia_inicio_poliza_' . $catCompartida, Poliza::CODIGO_INICIO[Poliza::CATEGORIA_OBRAS] ?? 0);

        $secuenciasPolizas[$catCompartida] = [
            'configurado' => $inicioDbCompartida,
            'ultimo' => $maxDbCompartida,
            'siguiente' => max($maxDbCompartida, $inicioDbCompartida) + 1,
            'label' => 'Pólizas de Obras y Proveedores',
        ];

        $mailConfig = [
            'mail_host' => config('mail.mailers.smtp.host') ?? env('MAIL_HOST', 'smtp.gmail.com'),
            'mail_port' => config('mail.mailers.smtp.port') ?? env('MAIL_PORT', 587),
            'mail_username' => config('mail.mailers.smtp.username') ?? env('MAIL_USERNAME', ''),
            'mail_password' => config('mail.mailers.smtp.password') ?? env('MAIL_PASSWORD', ''),
            'mail_encryption' => config('mail.mailers.smtp.encryption') ?? env('MAIL_ENCRYPTION', 'tls'),
            'mail_from_address' => config('mail.from.address') ?? env('MAIL_FROM_ADDRESS', ''),
            'mail_from_name' => config('mail.from.name') ?? env('MAIL_FROM_NAME', ''),
        ];

        $diasAnticipacion = (int) Configuracion::getValor('dias_anticipacion_oficio', 8);

        return Inertia::render('Configuraciones/Index', [
            'siguienteMinimoConfigurado' => $configMin,
            'ultimoOficioGenerado' => $maxDb,
            'siguienteCalculado' => $siguienteCalculado,
            'anio' => $anio,
            'secuenciasPolizas' => $secuenciasPolizas,
            'mailConfig' => $mailConfig,
            'diasAnticipacion' => $diasAnticipacion,
        ]);
    }

    /**
     * Guarda la configuración de la secuencia de oficios
     */
    public function guardarSecuenciaOficios(Request $request)
    {
        $request->validate([
            'siguiente_numero' => 'required|integer|min:1'
        ]);

        $anio = now()->year;
        $maxDb = Oficio::where('anio', $anio)->max('numero') ?? 0;

        $nuevoSiguiente = (int) $request->siguiente_numero;

        if ($nuevoSiguiente <= $maxDb) {
            return back()->with('error', "El número ingresado ($nuevoSiguiente) debe ser estrictamente mayor al último oficio ya generado en la base de datos ($maxDb) para evitar conflictos.");
        }

        Configuracion::setValor('siguiente_numero_oficio_' . $anio, $nuevoSiguiente);

        return back()->with('message', "Secuencia de oficios configurada correctamente. El próximo oficio a generar será el N° $nuevoSiguiente.");
    }

    /**
     * Guarda la configuración de la secuencia de códigos de pólizas
     */
    public function guardarSecuenciaPolizas(Request $request)
    {
        $request->validate([
            'categoria' => 'required|string|in:ambiental,obras_proveedores',
            'siguiente_numero' => 'required|integer|min:1'
        ]);

        $categoria = $request->categoria;

        if ($categoria === 'obras_proveedores') {
            $label = 'Pólizas de Obras y Proveedores';
            $maxDb = Poliza::withTrashed()
                ->whereIn('categoria_poliza', [Poliza::CATEGORIA_OBRAS, Poliza::CATEGORIA_PROVEEDORES])
                ->max('codigo') ?? 0;
        } else {
            $label = 'Pólizas Ambientales';
            $maxDb = Poliza::withTrashed()
                ->where('categoria_poliza', Poliza::CATEGORIA_AMBIENTAL)
                ->max('codigo') ?? 0;
        }

        $nuevoSiguiente = (int) $request->siguiente_numero;
        $nuevoInicio = $nuevoSiguiente - 1; // El inicio es lo que tomamos como "base"

        // No podemos configurar una base que nos de un siguiente menor o igual a lo que ya existe
        if ($nuevoInicio < $maxDb) {
            return back()->with('error', "El número ingresado ($nuevoSiguiente) debe ser estrictamente mayor al último código generado en la base de datos para $label (que es $maxDb) para evitar conflictos.");
        }

        Configuracion::setValor('secuencia_inicio_poliza_' . $categoria, $nuevoInicio);

        return back()->with('message', "Secuencia de códigos para $label configurada correctamente. La próxima póliza generada tendrá el código N° $nuevoSiguiente.");
    }

    /**
     * Guarda los días de anticipación para la generación automática de oficios
     */
    public function guardarDiasAnticipacion(Request $request)
    {
        $request->validate([
            'dias' => 'required|integer|min:1|max:60',
        ]);

        Configuracion::setValor('dias_anticipacion_oficio', (int) $request->dias);

        return back()->with('message', "Configuración actualizada: los oficios se generarán con {$request->dias} día(s) de anticipación al vencimiento.");
    }

    /**
     * Guarda la configuración del correo reescribiendo el .env
     */
    public function guardarConfiguracionCorreo(Request $request)
    {
        $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $str = file_get_contents($envFile);

            $updates = [
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username,
                'MAIL_PASSWORD' => '"' . trim($request->mail_password, '"') . '"',
                'MAIL_ENCRYPTION' => $request->mail_encryption ?: 'null',
                'MAIL_FROM_ADDRESS' => '"' . trim($request->mail_from_address, '"') . '"',
                'MAIL_FROM_NAME' => '"' . trim($request->mail_from_name, '"') . '"',
            ];

            foreach ($updates as $key => $value) {
                $pattern = "/^{$key}=(.*)$/m";
                if (preg_match($pattern, $str)) {
                    $str = preg_replace($pattern, "{$key}={$value}", $str);
                } else {
                    $str .= "\n{$key}={$value}";
                }
            }

            file_put_contents($envFile, $str);
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        }

        return back()->with('message', 'Configuración de correo (SMTP) actualizada correctamente.');
    }
}
