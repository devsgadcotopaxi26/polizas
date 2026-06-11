<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SucursalAseguradora extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('Sucursales');
    }
    protected $table = 'sucursales_aseguradora';

    protected $fillable = [
        'aseguradora_id',
        'ciudad_id',
        'nombre_contacto',
        'correo1', 'correo2', 'correo3', 'correo4', 'correo5', 'correo6',
        'celular1', 'celular2', 'celular3',
        'telefono_fijo1', 'telefono_fijo2',
        'extensiones',
    ];

    public function aseguradora(): BelongsTo
    {
        return $this->belongsTo(Aseguradora::class, 'aseguradora_id');
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    public function polizas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Poliza::class, 'sucursal_id');
    }
}
