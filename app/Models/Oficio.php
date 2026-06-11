<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oficio extends Model
{
    protected $table = 'oficios';

    protected $fillable = [
        'numero',
        'anio',
        'codigo_completo',
        'fecha_generacion',
    ];

    protected $casts = [
        'fecha_generacion' => 'date',
        'numero' => 'integer',
        'anio' => 'integer',
    ];

    /**
     * Genera el siguiente oficio para el año actual.
     * Retorna la instancia creada con su código completo.
     */
    public static function generarSiguiente(): self
    {
        $anio = now()->year;

        // Obtener el último número del año en curso
        $maxDb = self::where('anio', $anio)->max('numero') ?? 0;

        // Obtener la secuencia mínima configurada por el administrador (si existe)
        $configMin = (int) \App\Models\Configuracion::getValor('siguiente_numero_oficio_' . $anio, 0);

        $siguiente = max($maxDb + 1, $configMin);
        $numeroFormateado = str_pad($siguiente, 3, '0', STR_PAD_LEFT);

        $codigo = "Oficio N° {$numeroFormateado}-{$anio}-GADPC-T-POLIZA";

        return self::create([
            'numero' => $siguiente,
            'anio' => $anio,
            'codigo_completo' => $codigo,
            'fecha_generacion' => now()->toDateString(),
        ]);
    }
}
