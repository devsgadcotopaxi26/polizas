<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolizaHistorial extends Model
{
    protected $table = 'poliza_historial';

    protected $fillable = [
        'poliza_id',
        'accion',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'descripcion',
        'usuario_id',
    ];

    /**
     * Relación con Poliza
     */
    public function poliza()
    {
        return $this->belongsTo(Poliza::class);
    }

    /**
     * Relación con User
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    /**
     * Scope para filtrar por tipo de acción
     */
    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    /**
     * Scope para ordenar por más reciente
     */
    public function scopeRecientes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
