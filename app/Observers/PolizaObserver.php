<?php

namespace App\Observers;

use App\Models\Poliza;

class PolizaObserver
{
    /**
     * Handle the Poliza "created" event.
     */
    public function created(Poliza $poliza): void
    {
        \App\Models\PolizaHistorial::create([
            'poliza_id' => $poliza->id,
            'accion' => 'creada',
            'descripcion' => 'Póliza creada con número: ' . $poliza->numero_poliza,
            'usuario_id' => auth()->id(),
        ]);
    }

    /**
     * Handle the Poliza "updated" event.
     */
    public function updated(Poliza $poliza): void
    {
        // Usar getChanges() en vez de getDirty() porque updated se dispara DESPUÉS de guardar
        $cambios = $poliza->getChanges();

        // getOriginal() aún contiene los valores anteriores en este punto
        $original = $poliza->getOriginal();

        foreach ($cambios as $campo => $valorNuevo) {
            // Ignorar campos técnicos
            if (in_array($campo, ['updated_at', 'created_at'])) {
                continue;
            }

            $valorAnterior = $original[$campo] ?? null;

            // Detectar cambio de estado especialmente
            if ($campo === 'estado') {
                \App\Models\PolizaHistorial::create([
                    'poliza_id' => $poliza->id,
                    'accion' => 'estado_cambiado',
                    'campo_modificado' => $campo,
                    'valor_anterior' => $valorAnterior,
                    'valor_nuevo' => $valorNuevo,
                    'descripcion' => "Estado cambiado de '$valorAnterior' a '$valorNuevo'",
                    'usuario_id' => auth()->id(),
                ]);
            } else {
                // Registrar otros cambios
                \App\Models\PolizaHistorial::create([
                    'poliza_id' => $poliza->id,
                    'accion' => 'actualizada',
                    'campo_modificado' => $campo,
                    'valor_anterior' => is_array($valorAnterior) ? json_encode($valorAnterior) : $valorAnterior,
                    'valor_nuevo' => is_array($valorNuevo) ? json_encode($valorNuevo) : $valorNuevo,
                    'descripcion' => "Campo '$campo' modificado",
                    'usuario_id' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Handle the Poliza "deleted" event.
     */
    public function deleted(Poliza $poliza): void
    {
        \App\Models\PolizaHistorial::create([
            'poliza_id' => $poliza->id,
            'accion' => 'eliminada',
            'descripcion' => 'Póliza eliminada (soft delete)',
            'usuario_id' => auth()->id(),
        ]);
    }

    /**
     * Handle the Poliza "restored" event.
     */
    public function restored(Poliza $poliza): void
    {
        \App\Models\PolizaHistorial::create([
            'poliza_id' => $poliza->id,
            'accion' => 'restaurada',
            'descripcion' => 'Póliza restaurada',
            'usuario_id' => auth()->id(),
        ]);
    }

    /**
     * Handle the Poliza "force deleted" event.
     */
    public function forceDeleted(Poliza $poliza): void
    {
        // No registrar en historial porque la póliza será eliminada permanentemente
    }
}
