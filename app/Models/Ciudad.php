<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Ciudad extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('Ciudades');
    }
    protected $table = 'ciudades';

    protected $fillable = [
        'nombre',
    ];

    /**
     * Aseguradoras en esta ciudad
     */
    public function aseguradoras(): HasMany
    {
        return $this->hasMany(Aseguradora::class, 'ciudad_id');
    }
}
