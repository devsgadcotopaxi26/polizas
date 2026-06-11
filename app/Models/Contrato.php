<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contrato extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('Contratos');
    }
    protected $table = 'contratos';

    protected $fillable = [
        'numero_contrato',
        'objeto_contratacion',
        'valor_contrato',
        'anticipo',
        'contratista_id',
        'administrador_id',
    ];

    protected function casts(): array
    {
        return [
            'valor_contrato' => 'decimal:2',
            'anticipo' => 'decimal:2',
        ];
    }

    /**
     * Contratista del contrato
     */
    public function contratista(): BelongsTo
    {
        return $this->belongsTo(Contratista::class, 'contratista_id');
    }

    /**
     * Administrador del contrato
     */
    public function administrador(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'administrador_id');
    }

    /**
     * Pólizas del contrato
     */
    public function polizas(): HasMany
    {
        return $this->hasMany(Poliza::class, 'contrato_id');
    }
}
