<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Poliza extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Pólizas');
    }

    // Categorías de póliza
    const CATEGORIA_AMBIENTAL = 'ambiental';
    const CATEGORIA_OBRAS = 'obras';
    const CATEGORIA_PROVEEDORES = 'proveedores';

    // Subtipos de póliza
    const SUBTIPO_FIEL_CUMPLIMIENTO_AMBIENTAL = 'fiel_cumplimiento_ambiental';
    const SUBTIPO_FIEL_CUMPLIMIENTO = 'fiel_cumplimiento';
    const SUBTIPO_BUEN_USO = 'buen_uso';

    // Mapeo de categorías con sus subtipos válidos
    const CATEGORIAS_SUBTIPOS = [
        self::CATEGORIA_AMBIENTAL => [
            self::SUBTIPO_FIEL_CUMPLIMIENTO_AMBIENTAL,
        ],
        self::CATEGORIA_OBRAS => [
            self::SUBTIPO_FIEL_CUMPLIMIENTO,
            self::SUBTIPO_BUEN_USO,
        ],
        self::CATEGORIA_PROVEEDORES => [
            self::SUBTIPO_FIEL_CUMPLIMIENTO,
            self::SUBTIPO_BUEN_USO,
        ],
    ];

    // Labels para mostrar en la UI
    const CATEGORIA_LABELS = [
        self::CATEGORIA_AMBIENTAL => 'Pólizas Ambientales',
        self::CATEGORIA_OBRAS => 'Pólizas de Obras',
        self::CATEGORIA_PROVEEDORES => 'Pólizas de Proveedores',
    ];

    const SUBTIPO_LABELS = [
        self::SUBTIPO_FIEL_CUMPLIMIENTO_AMBIENTAL => 'Fiel Cumplimiento Ambiental',
        self::SUBTIPO_FIEL_CUMPLIMIENTO => 'Fiel Cumplimiento',
        self::SUBTIPO_BUEN_USO => 'Buen Uso',
    ];

    protected $table = 'polizas';

    protected $fillable = [
        'codigo',
        'numero_poliza',
        'categoria_poliza',
        'subtipo_poliza',
        'valor_asegurado',
        'fecha_inicio',
        'fecha_vencimiento',
        'sucursal_id',
        'contrato_id',
        'operador_ambiental_id',
        'codigo_proyecto_amb',
        'created_by',
        'estado',
        'fecha_acta_provisional',
        'fecha_acta_definitiva',
        'archivo_acta',
        'observaciones',
        'documentos_adjuntos',
        'notificacion_enviada',
        'fecha_ultima_notificacion',
        'oficio_id',
        'oficio_email_1_at',
        'oficio_email_2_at',
    ];

    // Código inicial por categoría (offset de inicio)
    const CODIGO_INICIO = [
        self::CATEGORIA_AMBIENTAL => 0,
        self::CATEGORIA_OBRAS => 745,
        self::CATEGORIA_PROVEEDORES => 745,
    ];

    /**
     * Atributos dinámicos que se agregarán a las respuestas JSON.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'estado_actual',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($poliza) {
            if (!$poliza->codigo) {
                // Las pólizas ambientales no llevan código secuencial
                if ($poliza->categoria_poliza === self::CATEGORIA_AMBIENTAL) {
                    return;
                }

                $isObrasOProveedores = in_array($poliza->categoria_poliza, [self::CATEGORIA_OBRAS, self::CATEGORIA_PROVEEDORES]);
                $categoriasQuery = $isObrasOProveedores
                    ? [self::CATEGORIA_OBRAS, self::CATEGORIA_PROVEEDORES]
                    : [$poliza->categoria_poliza];

                $configKeyName = $isObrasOProveedores ? 'obras_proveedores' : $poliza->categoria_poliza;

                $maxCodigo = static::withTrashed()
                    ->whereIn('categoria_poliza', $categoriasQuery)
                    ->max('codigo');

                $inicioDb = (int) \App\Models\Configuracion::getValor(
                    'secuencia_inicio_poliza_' . $configKeyName,
                    self::CODIGO_INICIO[$poliza->categoria_poliza] ?? 0
                );

                $poliza->codigo = max($maxCodigo ?? 0, $inicioDb) + 1;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'valor_asegurado' => 'decimal:2',
            'fecha_inicio' => 'datetime:Y-m-d H:i:s',
            'fecha_vencimiento' => 'datetime:Y-m-d H:i:s',
            'fecha_acta_provisional' => 'datetime:Y-m-d H:i:s',
            'fecha_acta_definitiva' => 'datetime:Y-m-d H:i:s',
            'notificacion_enviada' => 'boolean',
            'fecha_ultima_notificacion' => 'datetime',
            'documentos_adjuntos' => 'array', // JSON cast
        ];
    }

    /**
     * Calcula automáticamente el estado vencido si la fecha ya pasó
     * y el estado original no está cerrado.
     */
    public function getEstadoActualAttribute()
    {
        // Estados activos que pueden vencer
        if (in_array($this->estado, ['vigente', 'acta_provisional', 'original'])) {
            if ($this->fecha_vencimiento && \Carbon\Carbon::parse($this->fecha_vencimiento)->startOfDay()->lt(now()->startOfDay())) {
                // Si es estado "original", verificamos si no tiene renovaciones (es decir, fue forzada manualmente a original
                // pero no le han creado una verdadera renovación todavía).
                if ($this->estado === 'original') {
                    $tieneRenovaciones = \App\Models\PolizaRenovacion::where('poliza_original_id', $this->id)->exists();
                    if (!$tieneRenovaciones) {
                        return 'vencida';
                    }
                } else {
                    // Para vigente y acta provisinal, vence directamente
                    return 'vencida';
                }
            }
        }

        return $this->estado;
    }

    /**
     * Sucursal de la aseguradora a la que pertenece la póliza
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(SucursalAseguradora::class, 'sucursal_id');
    }

    /**
     * Contrato asociado a la póliza
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    /**
     * Operador Ambiental asociado a la póliza (para pólizas ambientales)
     */
    public function operadorAmbiental(): BelongsTo
    {
        return $this->belongsTo(OperadorAmbiental::class, 'operador_ambiental_id');
    }

    /**
     * Oficio de renovación asociado a la póliza
     */
    public function oficio(): BelongsTo
    {
        return $this->belongsTo(Oficio::class);
    }

    /**
     * Usuario que creó la póliza
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para pólizas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'vigente');
    }

    /**
     * Scope para pólizas próximas a vencer
     */
    public function scopeProximasVencer($query, $dias = 30)
    {
        return $query->where('estado', 'vigente')
            ->whereBetween('fecha_vencimiento', [
                now(),
                now()->addDays($dias)
            ]);
    }

    /**
     * Calcular días hasta el vencimiento
     */
    public function diasParaVencer()
    {
        return now()->diffInDays($this->fecha_vencimiento, false);
    }

    /**
     * Historial de cambios de esta póliza
     */
    public function historial()
    {
        return $this->hasMany(PolizaHistorial::class);
    }

    /**
     * Renovaciones donde esta póliza es la original
     */
    public function renovacionesHechas()
    {
        return $this->hasMany(PolizaRenovacion::class, 'poliza_original_id');
    }

    /**
     * Renovación donde esta póliza es la nueva (fue creada a partir de otra)
     */
    public function renovacionDe()
    {
        return $this->hasOne(PolizaRenovacion::class, 'poliza_nueva_id');
    }

    /**
     * Obtener la póliza original si esta es una renovación
     */
    public function polizaOriginal()
    {
        $renovacion = $this->renovacionDe;
        return $renovacion ? $renovacion->polizaOriginal : null;
    }

    /**
     * Contar cuántas veces se ha renovado esta póliza
     */
    public function contarRenovaciones()
    {
        $count = 0;
        $polizaActual = $this;

        while ($renovacion = $polizaActual->renovacionesHechas()->first()) {
            $count++;
            $polizaActual = $renovacion->polizaNueva;
        }

        return $count;
    }

    /**
     * Verificar si esta póliza fue renovada
     */
    public function fueRenovada()
    {
        return $this->renovacionesHechas()->exists();
    }
}
