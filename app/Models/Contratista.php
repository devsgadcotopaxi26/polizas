<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contratista extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('Contratistas');
    }
    protected $table = 'contratistas';

    protected $fillable = [
        'nombre_cont',
        'correo_cont',
        'celular_cont',
        'telefono_fijo',
        'taxid',
    ];

    /**
     * Contratos del contratista
     */
    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'contratista_id');
    }
}
