<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerarOficiosBaseAutomaticamente extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polizas:generar-oficios-base';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera el oficio base automáticamente para pólizas que vencen en 8 días o menos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Comando pausado temporalmente por mantenimiento de secuencias.');
        return;
        
        $dias = (int) \App\Models\Configuracion::getValor('dias_anticipacion_oficio', 8);
        $today = now()->startOfDay();
        $targetDate = now()->addDays($dias)->endOfDay();

        $this->info("Generando oficios para pólizas que vencen en los próximos {$dias} días.");

        // Obtener pólizas vigentes (no ambientales), que vencen dentro del umbral, y que AÚN NO tienen un oficio generado
        $polizas = \App\Models\Poliza::where('estado', 'vigente')
            ->where('categoria_poliza', '!=', 'ambiental')
            ->whereBetween('fecha_vencimiento', [$today, $targetDate])
            ->whereNull('oficio_path')
            ->get();

        if ($polizas->isEmpty()) {
            $this->info('No hay pólizas que requieran generar oficio base.');
            return;
        }

        $this->info('Se encontraron ' . $polizas->count() . ' pólizas para procesar.');

        foreach ($polizas as $poliza) {
            try {
                // Cargar relaciones necesarias para la vista
                $poliza->load(['sucursal.aseguradora', 'sucursal.ciudad', 'contrato.contratista', 'contrato.administrador', 'usuario']);

                // Generar un nuevo oficio con número incremental real
                // $oficio = \App\Models\Oficio::generarSiguiente();
                // $numeroOficio = $oficio->codigo_completo;
                $numeroOficio = 'PENDIENTE';

                $fileName = 'oficios/Oficio_Renovacion_' . $poliza->id . '.pdf';

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.oficio_renovacion', compact('poliza', 'numeroOficio'));
                $pdf->setPaper('A4', 'portrait');
                $pdfContent = $pdf->output();

                // Guardar el documento base 
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $pdfContent);

                $poliza->oficio_path = $fileName;
                // $poliza->oficio_id = $oficio->id;
                $poliza->save();

                $this->info('Oficio base generado exitosamente para póliza: ' . $poliza->numero_poliza);

            } catch (\Exception $e) {
                $this->error('Error generando oficio para póliza ' . $poliza->numero_poliza . ': ' . $e->getMessage());
                \Illuminate\Support\Facades\Log::error('Error auto-generando oficio de póliza ' . $poliza->numero_poliza . ': ' . $e->getMessage());
            }
        }

        $this->info('Proceso de auto-generación de oficios finalizado.');
    }
}
