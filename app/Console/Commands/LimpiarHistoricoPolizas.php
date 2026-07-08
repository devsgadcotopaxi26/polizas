<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LimpiarHistoricoPolizas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polizas:limpiar-historico {--fecha=2026-06-29 : Fecha límite (Y-m-d) para marcar pólizas antiguas como completadas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca todas las pólizas y renovaciones creadas antes de la fecha indicada como firmadas y enviadas.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fechaInput = $this->option('fecha');
        $fecha = \Carbon\Carbon::parse($fechaInput)->startOfDay();

        $this->info("Iniciando limpieza masiva de bandejas para pólizas creadas antes de: " . $fecha->toDateString());

        if (!$this->confirm('¿Estás seguro de continuar? Esto alterará el historial de firmas y notificaciones en BD.')) {
            $this->warn('Operación cancelada.');
            return;
        }

        $oficios = \App\Models\Poliza::where('created_at', '<', $fecha)
            ->whereNotNull('oficio_path')
            ->update([
                'oficio_firmado_gestor' => true,
                'oficio_firmado_tesorero' => true,
                'oficio_email_1_at' => now(),
                'oficio_email_2_at' => now()
            ]);

        $renovaciones = \App\Models\PolizaRenovacion::where('created_at', '<', $fecha)
            ->where('estado_firma_asesor', false)
            ->update([
                'estado_firma_asesor' => true
            ]);

        $this->info("✅ Limpieza completada con éxito:");
        $this->line("- Oficios actualizados (Bandeja Gestor/Tesorero): {$oficios}");
        $this->line("- Renovaciones actualizadas (Bandeja Prefecta): {$renovaciones}");
    }
}
