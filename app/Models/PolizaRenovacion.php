<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PolizaRenovacion extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('Renovaciones');
    }
    protected $table = 'poliza_renovaciones';

    protected $fillable = [
        'poliza_original_id',
        'poliza_nueva_id',
        'fecha_renovacion',
        'tipo_renovacion',
        'observaciones',
        'usuario_id',
        'archivo_renovacion',
        'estado_firma_asesor',
    ];

    protected $casts = [
        'fecha_renovacion' => 'date',
        'estado_firma_asesor' => 'boolean',
    ];

    /**
     * Relación con la póliza original
     */
    public function polizaOriginal()
    {
        return $this->belongsTo(Poliza::class, 'poliza_original_id');
    }

    /**
     * Relación con la póliza nueva
     */
    public function polizaNueva()
    {
        return $this->belongsTo(Poliza::class, 'poliza_nueva_id');
    }

    /**
     * Relación con User
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }
}
