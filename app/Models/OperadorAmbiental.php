<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class OperadorAmbiental extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('Operadores Ambientales');
    }
    protected $table = 'operadores_ambientales';

    protected $fillable = [
        'nombre',
        'empresa',
        'correo',
        'celular',
        'telefono_fijo',
        'taxid',
    ];
}
