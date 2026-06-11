<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Aseguradora extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('Aseguradoras');
    }
    protected $table = 'aseguradoras';

    protected $fillable = [
        'nombre_empresa',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Sucursales de esta aseguradora (una por ciudad)
     */
    public function sucursales(): HasMany
    {
        return $this->hasMany(SucursalAseguradora::class, 'aseguradora_id')->with('ciudad');
    }
}
