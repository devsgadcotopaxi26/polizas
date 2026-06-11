<?php

namespace App\Http\Controllers;

use App\Models\Poliza;
use App\Models\SucursalAseguradora;
use App\Models\Contrato;
use App\Models\Oficio;
use App\Mail\OficioMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;


class PolizaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Poliza::with(['sucursal.aseguradora', 'sucursal.ciudad', 'usuario', 'contrato.contratista', 'operadorAmbiental', 'renovacionDe']);

        // El rol Gestor Tesorería Ambiente solo puede ver pólizas ambientales
        if (Auth::user()->hasRole('Gestor Tesorería Ambiente')) {
            $query->where('categoria_poliza', 'ambiental');
        }

        // Filtros
        if ($request->has('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('numero_poliza', 'like', $search)
                    ->orWhere('codigo', 'like', $search)
                    ->orWhereHas('contrato.contratista', function ($q2) use ($search) {
                        $q2->where('nombre_cont', 'like', $search);
                    });
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'vencida') {
                $query->whereIn('estado', ['vigente', 'acta_provisional', 'original'])
                      ->whereNotNull('fecha_vencimiento')
                      ->where('fecha_vencimiento', '<', now()->startOfDay());
            } elseif (in_array($request->estado, ['vigente', 'acta_provisional', 'original'])) {
                $query->where('estado', $request->estado)
                      ->where(function ($q) {
                          $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->startOfDay());
                      });
            } else {
                $query->where('estado', $request->estado);
            }
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_poliza', $request->categoria);
        }

        if ($request->filled('subtipo')) {
            $query->where('subtipo_poliza', $request->subtipo);
        }

        if ($request->filled('bandeja_tesorero')) {
            $query->whereNotNull('oficio_path')
                ->where('oficio_firmado_gestor', true)
                ->where('oficio_firmado_tesorero', false);
        }

        if ($request->filled('bandeja_asesor')) {
            $query->whereHas('renovacionDe', function ($q) {
                // Si la póliza tiene un registro en 'renovacion_nueva_id' de PolizaRenovacion 
                // entonces ES una renovación. 
                $q->whereNotNull('archivo_renovacion')
                    ->where('estado_firma_asesor', false);
            });
        }

        if ($request->filled('bandeja_gestor_envio')) {
            $query->whereNotNull('oficio_path')
                ->where('oficio_firmado_gestor', true)
                ->where('oficio_firmado_tesorero', true);
        }

        $sortableColumns = ['codigo', 'numero_poliza', 'fecha_vencimiento', 'valor_asegurado', 'estado', 'created_at'];
        $sortBy  = in_array($request->sort_by, $sortableColumns) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';

        $polizas = $query->orderBy($sortBy, $sortDir)->paginate(10)->withQueryString();

        return Inertia::render('Polizas/Index', [
            'polizas'           => $polizas,
            'filters'           => $request->only(['search', 'estado', 'categoria', 'subtipo', 'bandeja_tesorero', 'bandeja_asesor', 'bandeja_gestor_envio', 'sort_by', 'sort_dir']),
            'esGestorAmbiental' => Auth::user()->hasRole('Gestor Tesorería Ambiente'),
            'esAsesor'          => Auth::user()->hasRole('Asesor Prefectura'),
        ]);
    }

    /**
     * Devuelve el query builder con los filtros aplicados (Para index y exports)
     */
    private function getFilteredQuery(Request $request)
    {
        $query = Poliza::with(['sucursal.aseguradora', 'sucursal.ciudad', 'usuario', 'contrato.contratista', 'operadorAmbiental']);

        // El rol Gestor Tesorería Ambiente solo puede ver pólizas ambientales
        if (Auth::user()->hasRole('Gestor Tesorería Ambiente')) {
            $query->where('categoria_poliza', 'ambiental');
        }

        if ($request->has('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('numero_poliza', 'like', $search)
                    ->orWhere('codigo', 'like', $search)
                    ->orWhereHas('contrato.contratista', function ($q2) use ($search) {
                        $q2->where('nombre_cont', 'like', $search);
                    });
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'vencida') {
                $query->whereIn('estado', ['vigente', 'acta_provisional', 'original'])
                      ->whereNotNull('fecha_vencimiento')
                      ->where('fecha_vencimiento', '<', now()->startOfDay());
            } elseif (in_array($request->estado, ['vigente', 'acta_provisional', 'original'])) {
                $query->where('estado', $request->estado)
                      ->where(function ($q) {
                          $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->startOfDay());
                      });
            } else {
                $query->where('estado', $request->estado);
            }
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_poliza', $request->categoria);
        }

        if ($request->filled('subtipo')) {
            $query->where('subtipo_poliza', $request->subtipo);
        }

        if ($request->filled('bandeja_tesorero')) {
            $query->whereNotNull('oficio_path')
                ->where('oficio_firmado_gestor', true)
                ->where('oficio_firmado_tesorero', false);
        }

        if ($request->filled('bandeja_asesor')) {
            $query->whereHas('renovacionDe', function ($q) {
                $q->whereNotNull('archivo_renovacion')
                    ->where('estado_firma_asesor', false);
            });
        }

        if ($request->filled('bandeja_gestor_envio')) {
            $query->whereNotNull('oficio_path')
                ->where('oficio_firmado_gestor', true)
                ->where('oficio_firmado_tesorero', true);
        }

        return $query->latest();
    }

    /**
     * Exportaciones
     */
    public function exportExcel(Request $request)
    {
        $polizas = $this->getFilteredQuery($request)->get();

        $fileName = 'polizas_' . date('Y_m_d_H_i') . '.xlsx';
        $filePath = storage_path('app/' . $fileName);

        $writer = new \OpenSpout\Writer\XLSX\Writer();
        $writer->openToFile($filePath);

        // Estilo para encabezados
        $headerStyle = new \OpenSpout\Common\Entity\Style\Style();
        $headerStyle->setFontBold();
        $headerStyle->setFontSize(11);
        $headerStyle->setFontColor(\OpenSpout\Common\Entity\Style\Color::WHITE);
        $headerStyle->setBackgroundColor('024283');

        $esGestorAmbiental = Auth::user()->hasRole('Gestor Tesorería Ambiente');

        // Escribir encabezados
        $headerRow = \OpenSpout\Common\Entity\Row::fromValues([
            'N° Póliza', 'Categoría', 'Subtipo', 'Valor Asegurado',
            'Fecha Inicio', 'Fecha Vencimiento', 
            $esGestorAmbiental ? 'Operador Ambiental' : 'Contratista',
            'Aseguradora', 'Estado',
        ], $headerStyle);
        $writer->addRow($headerRow);

        // Escribir datos
        foreach ($polizas as $poliza) {
            if ($esGestorAmbiental) {
                $entidad = $poliza->operadorAmbiental ? $poliza->operadorAmbiental->nombre : 'N/A';
            } else {
                $entidad = $poliza->contrato && $poliza->contrato->contratista
                    ? $poliza->contrato->contratista->nombre_cont
                    : 'N/A';
            }

            $row = \OpenSpout\Common\Entity\Row::fromValues([
                $poliza->numero_poliza,
                ucfirst($poliza->categoria_poliza),
                str_replace('_', ' ', ucfirst($poliza->subtipo_poliza)),
                $poliza->valor_asegurado,
                $poliza->fecha_inicio->format('d/m/Y'),
                $poliza->fecha_vencimiento->format('d/m/Y'),
                $entidad,
                $poliza->sucursal && $poliza->sucursal->aseguradora ? $poliza->sucursal->aseguradora->nombre_empresa : 'N/A',
                ucfirst(str_replace('_', ' ', $poliza->estado)),
            ]);
            $writer->addRow($row);
        }

        $writer->close();

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $polizas = $this->getFilteredQuery($request)->get();
        $generadoPor = Auth::user()->name;
        $esGestorAmbiental = Auth::user()->hasRole('Gestor Tesorería Ambiente');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.pdf_polizas', compact('polizas', 'generadoPor', 'esGestorAmbiental'))
            ->setPaper('A4', 'landscape');
            
        return $pdf->stream('polizas_' . date('Y_m_d_H_i') . '.pdf');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Polizas/Create', [
            'aseguradoras' => SucursalAseguradora::with(['aseguradora', 'ciudad'])->whereHas('aseguradora', function($q) { $q->where('activo', true); })->get(),
            'contratos' => Contrato::with('contratista')->get(),
            'operadores_ambientales' => \App\Models\OperadorAmbiental::orderBy('nombre')->get(),
            'categorias_subtipos' => Poliza::CATEGORIAS_SUBTIPOS,
            'categoria_labels' => Poliza::CATEGORIA_LABELS,
            'subtipo_labels' => Poliza::SUBTIPO_LABELS,
            'esGestorAmbiental' => \Illuminate\Support\Facades\Auth::user()->hasRole('Gestor Tesorería Ambiente'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|integer',
            'numero_poliza' => 'required|string|unique:polizas',
            'categoria_poliza' => 'required|in:ambiental,obras,proveedores',
            'subtipo_poliza' => 'required|in:fiel_cumplimiento_ambiental,fiel_cumplimiento,buen_uso',
            'valor_asegurado' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_inicio',
            'contrato_id' => 'nullable|exists:contratos,id',
            'operador_ambiental_id' => 'nullable|exists:operadores_ambientales,id',
            'codigo_proyecto_amb' => 'nullable|string|max:255',
            'sucursal_id' => 'required|exists:sucursales_aseguradora,id',
            'observaciones' => 'nullable|string',
            'estado' => 'nullable|in:vigente,acta_provisional,acta_definitiva,liquidada,original,renovada',
            'fecha_acta_provisional' => 'nullable|date',
            'fecha_acta_definitiva' => 'nullable|date',
            'archivo_acta' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $validated['created_by'] = Auth::id();
        if (!isset($validated['estado'])) {
            $validated['estado'] = 'vigente';
        }

        if ($request->hasFile('archivo_acta')) {
            $validated['archivo_acta'] = $request->file('archivo_acta')->store('actas', 'public');
        }

        Poliza::create($validated);

        return redirect()->route('polizas.index')->with('message', 'Póliza creada exitosamente.');
    }

    /**
    /**
     * Display the specified resource.
     */
    public function show(Poliza $poliza)
    {
        $poliza->load(['sucursal.aseguradora', 'sucursal.ciudad', 'usuario', 'contrato.contratista', 'contrato.administrador', 'operadorAmbiental']);

        $renovacionDe = $poliza->renovacionDe()->with('polizaOriginal')->first();
        $renovacionHecha = $poliza->renovacionesHechas()
            ->with('polizaNueva')
            ->orderBy('id', 'desc')
            ->first();

        $historialRenovaciones = Poliza::where('codigo', $poliza->codigo)
            ->where('categoria_poliza', $poliza->categoria_poliza)
            ->where('subtipo_poliza', $poliza->subtipo_poliza)
            ->where('id', '!=', $poliza->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Polizas/Show', [
            'poliza'                 => $poliza,
            'renovacion_de'          => $renovacionDe,
            'renovacion_hecha'       => $renovacionHecha,
            'historial_renovaciones' => $historialRenovaciones,
            'contador_renovaciones'  => $poliza->contarRenovaciones(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Poliza $poliza)
    {
        if (Auth::user()->hasRole('Asesor Prefectura')) {
            return redirect()->route('polizas.show', $poliza->id)
                ->with('error', 'No tienes permisos para editar pólizas.');
        }

        // Cargar información de renovación (si esta póliza ES una renovación)
        $renovacionDe = $poliza->renovacionDe()->with('polizaOriginal')->first();

        return Inertia::render('Polizas/Edit', [
            'poliza' => $poliza,
            'renovacion_de' => $renovacionDe,
            'aseguradoras' => SucursalAseguradora::with(['aseguradora', 'ciudad'])->whereHas('aseguradora', function($q) { $q->where('activo', true); })->get(),
            'contratos' => Contrato::with('contratista')->get(),
            'operadores_ambientales' => \App\Models\OperadorAmbiental::orderBy('nombre')->get(),
            'categorias_subtipos' => Poliza::CATEGORIAS_SUBTIPOS,
            'categoria_labels' => Poliza::CATEGORIA_LABELS,
            'subtipo_labels' => Poliza::SUBTIPO_LABELS,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Poliza $poliza)
    {
        if (Auth::user()->hasRole('Asesor Prefectura')) {
            return response()->json(['message' => 'No tienes permisos para editar pólizas.'], 403);
        }

        $validated = $request->validate([
            'numero_poliza' => 'required|string|unique:polizas,numero_poliza,' . $poliza->id,
            'categoria_poliza' => 'required|in:ambiental,obras,proveedores',
            'subtipo_poliza' => 'required|in:fiel_cumplimiento_ambiental,fiel_cumplimiento,buen_uso',
            'valor_asegurado' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_inicio',
            'contrato_id' => 'nullable|exists:contratos,id',
            'operador_ambiental_id' => 'nullable|exists:operadores_ambientales,id',
            'codigo_proyecto_amb' => 'nullable|string|max:255',
            'sucursal_id' => 'required|exists:sucursales_aseguradora,id',
            'estado' => 'required|in:vigente,acta_provisional,acta_definitiva,liquidada,original,renovada',
            'observaciones' => 'nullable|string',
            'fecha_acta_provisional' => 'nullable|date',
            'fecha_acta_definitiva' => 'nullable|date',
            'archivo_acta' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'archivo_renovacion' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('archivo_acta')) {
            if ($poliza->archivo_acta) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($poliza->archivo_acta);
            }
            $validated['archivo_acta'] = $request->file('archivo_acta')->store('actas', 'public');
        }

        // Manejar reemplazo del PDF de renovación
        if ($request->hasFile('archivo_renovacion')) {
            $renovacion = $poliza->renovacionDe;
            if ($renovacion) {
                // Eliminar el archivo anterior si existe
                if ($renovacion->archivo_renovacion) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($renovacion->archivo_renovacion);
                }
                // Guardar el nuevo archivo
                $nuevoPath = $request->file('archivo_renovacion')->store('renovaciones', 'public');
                // Resetear firma al reemplazar el PDF
                $renovacion->update([
                    'archivo_renovacion' => $nuevoPath,
                    'estado_firma_asesor' => false,
                ]);
            }
        }

        // Quitar archivo_renovacion del array validated ya que se maneja aparte
        unset($validated['archivo_renovacion']);

        $poliza->update($validated);

        return redirect()->route('polizas.show', $poliza->id)->with('message', 'Póliza actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Poliza $poliza)
    {
        $poliza->delete();
        return redirect()->route('polizas.index')->with('message', 'Póliza eliminada exitosamente.');
    }

    /**
     * Eliminar una renovación específica.
     * Solo accesible para Administrador y Gestor de Tesorería.
     */
    public function eliminarRenovacion(Poliza $polizaNueva)
    {
        // Verificar rol
        $user = Auth::user();
        $roles = $user->getRoleNames();
        if (!$roles->contains('Administrador') && !$roles->contains('Super Admin') && !$roles->contains('Gestor de Tesorería')) {
            return redirect()->back()->with('error', 'No tienes permisos para eliminar renovaciones.');
        }

        // La póliza que se va a eliminar debe ser efectivamente una renovación
        $renovacion = \App\Models\PolizaRenovacion::where('poliza_nueva_id', $polizaNueva->id)->first();
        if (!$renovacion) {
            return redirect()->back()->with('error', 'Esta póliza no es una renovación.');
        }

        $polizaAnterior = $renovacion->polizaOriginal;

        // Eliminar el archivo PDF de renovación del storage si existe
        if ($renovacion->archivo_renovacion) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($renovacion->archivo_renovacion);
        }

        // Eliminar el registro de renovación
        $renovacion->delete();

        // Eliminar físicamente la póliza sin disparar el Observer (evita error FK en poliza_historial)
        Poliza::withoutObservers(function () use ($polizaNueva) {
            $polizaNueva->forceDelete();
        });

        // Si la póliza anterior ya no tiene más renovaciones, revertir su estado a vigente
        if ($polizaAnterior) {
            $tieneRenovacionPosterior = \App\Models\PolizaRenovacion::where('poliza_original_id', $polizaAnterior->id)->exists();
            if (!$tieneRenovacionPosterior) {
                Poliza::where('id', $polizaAnterior->id)->update(['estado' => 'vigente']);
            }

            return redirect()
                ->route('polizas.show', $polizaAnterior->id)
                ->with('message', 'Renovación eliminada exitosamente.');
        }

        return redirect()
            ->route('polizas.index')
            ->with('message', 'Renovación eliminada exitosamente.');
    }

    /**
     * Renovar una póliza creando una nueva a partir de la actual
     */
    public function renovar(Request $request, Poliza $poliza)
    {
        $validated = $request->validate([
            'valor_asegurado' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento' => 'required|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string',
            'archivo_renovacion' => 'nullable|file|mimes:pdf|max:10240', // 10MB máximo
        ]);

        // Validar que la póliza se puede renovar
        if (!in_array($poliza->estado, ['vigente', 'acta_provisional', 'acta_definitiva'])) {
            return redirect()->back()->with('error', 'Solo se pueden renovar pólizas vigentes o en actas.');
        }

        // Clonar los datos de la póliza original
        $nuevaPolizaData = $poliza->only([
            'categoria_poliza',
            'subtipo_poliza',
            'contrato_id',
            'sucursal_id',
        ]);

        // Aplicar los nuevos datos ingresados en el modal
        $nuevaPolizaData['valor_asegurado'] = $validated['valor_asegurado'];
        $nuevaPolizaData['fecha_inicio'] = $validated['fecha_inicio'];
        $nuevaPolizaData['fecha_vencimiento'] = $validated['fecha_vencimiento'];
        $nuevaPolizaData['observaciones'] = $validated['observaciones'];
        $nuevaPolizaData['estado'] = 'vigente';
        $nuevaPolizaData['created_by'] = Auth::id();

        // Mantener el código original de la póliza madre
        $nuevaPolizaData['codigo'] = $poliza->codigo;

        // Extraer el número base eliminando sufijos anteriores de renovación (-r1, -R1, -r2, -R2, etc)
        $numeroBase = preg_replace('/-[rR]\d+$/', '', $poliza->numero_poliza);

        // El número de esta renovación es igual a la cantidad actual de pólizas con este mismo código y subtipo
        $numeroRenovacion = \App\Models\Poliza::withTrashed()
            ->where('codigo', $poliza->codigo)
            ->where('categoria_poliza', $poliza->categoria_poliza)
            ->where('subtipo_poliza', $poliza->subtipo_poliza)
            ->count();

        $nuevaPolizaData['numero_poliza'] = $numeroBase . '-R' . $numeroRenovacion;

        // Validar que el número de póliza generado no exista para evitar el error 500
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['numero_poliza' => $nuevaPolizaData['numero_poliza']],
            ['numero_poliza' => 'unique:polizas,numero_poliza']
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', "No se puede completar la renovación porque el número de póliza generado ({$nuevaPolizaData['numero_poliza']}) ya existe en el sistema.");
        }

        // Crear la nueva póliza
        $nuevaPoliza = \App\Models\Poliza::create($nuevaPolizaData);

        // Crear registro de renovación
        $renovacionData = [
            'poliza_original_id' => $poliza->id,
            'poliza_nueva_id' => $nuevaPoliza->id,
            'fecha_renovacion' => now(),
            'tipo_renovacion' => 'manual',
            'observaciones' => $validated['observaciones'] ?: 'Renovación manual de póliza ' . $poliza->numero_poliza,
            'usuario_id' => Auth::id(),
            'estado_firma_asesor' => false,
        ];

        if ($request->hasFile('archivo_renovacion')) {
            $renovacionData['archivo_renovacion'] = $request->file('archivo_renovacion')->store('renovaciones', 'public');
        }

        \App\Models\PolizaRenovacion::create($renovacionData);

        // Determinar el nuevo estado de la póliza que se acaba de renovar
        // Si no fue renovación de otra, es la "madre", y su estado pasa a "original"
        // Si ya era una renovación (tenía polizaOriginal()), pasa a "renovada"
        $nuevoEstado = $poliza->renovacionDe ? 'renovada' : 'original';
        $poliza->update(['estado' => $nuevoEstado]);

        return redirect()
            ->route('polizas.show', $nuevaPoliza->id)
            ->with('message', 'Póliza renovada exitosamente. Número de nueva póliza: ' . $nuevaPoliza->numero_poliza);
    }

    /**
     * Generar PDF del oficio de renovación (con o sin firma criptográfica)
     */
    public function generarOficioPdf(\Illuminate\Http\Request $request, Poliza $poliza)
    {
        $poliza->load(['sucursal.aseguradora', 'sucursal.ciudad', 'contrato.contratista', 'contrato.administrador', 'usuario']);

        // 1. Obtener o Generar el Documento Base
        if ($poliza->oficio_path && \Storage::exists($poliza->oficio_path)) {
            $pdfContent = \Storage::get($poliza->oficio_path);
            // Recuperar el número del oficio ya guardado
            $numeroOficio = $poliza->oficio
                ? $poliza->oficio->codigo_completo
                : ($poliza->id . '-' . date('Y') . '-GADPC-T-POLIZA');
        } else {
            // Generar un nuevo oficio con número incremental real
            $oficio = Oficio::generarSiguiente();
            $numeroOficio = $oficio->codigo_completo;

            $fileName = 'oficios/Oficio_Renovacion_' . $poliza->id . '.pdf';

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.oficio_renovacion', compact('poliza', 'numeroOficio'));
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();

            // Guardar el documento base
            \Storage::put($fileName, $pdfContent);
            $poliza->oficio_path = $fileName;
            $poliza->oficio_id = $oficio->id;
            $poliza->save();
        }

        // 2. Si la petición es POST, significa que el usuario quiere FIRMAR el documento
        if ($request->isMethod('post')) {
            $request->validate([
                'password_certificado' => 'required|string',
                'sig_x' => 'nullable|numeric',
                'sig_y' => 'nullable|numeric',
                'sig_page' => 'nullable|integer',
            ]);

            $user = auth()->user();

            if (!$user->certificado_path) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['message' => 'No tienes un certificado configurado. Súbelo en tu perfil primero.'], 422);
                }
                return back()->with('error', 'No tienes un certificado configurado en tu perfil.');
            }

            try {
                $signService = new \App\Services\SignPdfService();
                $signedPdfString = $signService->signPdfString(
                    $pdfContent, // Pasamos el contenido existente (que ya puede tener 1 firma)
                    $user->certificado_path,
                    $request->password_certificado,
                    $user->name,
                    $user->getRoleNames()->toArray(),
                    'Notificación de Vencimiento de Póliza',
                    $request->sig_x,
                    $request->sig_y,
                    $request->sig_page
                );

                // Reemplazar el archivo en el storage con la nueva versión firmada
                \Storage::put($poliza->oficio_path, $signedPdfString);

                // Actualizar banderines en la base de datos
                $roles = $user->getRoleNames();
                if ($roles->contains('Gestor de Tesorería')) {
                    $poliza->oficio_firmado_gestor = true;
                }
                if ($roles->contains('Tesorero')) {
                    $poliza->oficio_firmado_tesorero = true;
                }
                $poliza->save();

                // Devolver el documento firmado
                return response($signedPdfString)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="Oficio_Renovacion_' . $poliza->numero_poliza . '_Firmado.pdf"');

            } catch (\Exception $e) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Contraseña incorrecta o error al firmar: ' . $e->getMessage()], 422);
                }
                return back()->with('error', 'Error al firmar: ' . $e->getMessage());
            }
        }

        // Si es GET, solo devuelve el PDF actual (sea el base o uno parcialmente firmado)
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Oficio_Renovacion_' . $poliza->numero_poliza . '.pdf"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Regenera el PDF del oficio usando el mismo ID/Número generado anteriormente,
     * siempre y cuando no existan firmas criptográficas plasmadas.
     */
    public function regenerarOficio(Poliza $poliza)
    {
        if ($poliza->oficio_firmado_gestor || $poliza->oficio_firmado_tesorero) {
            return redirect()->back()->with('error', 'No se puede regenerar el oficio porque ya tiene firmas. Cancela o reversa las firmas si es absolutamente necesario.');
        }

        if (!$poliza->oficio_path || !$poliza->oficio_id) {
            return redirect()->back()->with('error', 'No hay un oficio generado para regenerar.');
        }

        $poliza->load(['sucursal.aseguradora', 'sucursal.ciudad', 'contrato.contratista', 'contrato.administrador', 'usuario', 'oficio']);

        $numeroOficio = $poliza->oficio->codigo_completo;
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.oficio_renovacion', compact('poliza', 'numeroOficio'));
        $pdf->setPaper('A4', 'portrait');

        // Sobrescribir el archivo en disco (usando el mismo disco por defecto que al crear)
        \Storage::put($poliza->oficio_path, $pdf->output());

        return redirect()->back()->with('message', 'Oficio regenerado exitosamente con la información más reciente.');
    }

    /**
     * Devuelve el PDF temporal (o original) para el visor de la renovación
     */
    public function getPdfRenovacion(Poliza $poliza)
    {
        $renovacion = \App\Models\PolizaRenovacion::where('poliza_nueva_id', $poliza->id)->firstOrFail();

        if (!$renovacion->archivo_renovacion) {
            abort(404, 'No hay documento de renovación subido.');
        }

        $path = storage_path('app/public/' . $renovacion->archivo_renovacion);
        if (!file_exists($path)) {
            abort(404, 'Archivo no encontrado en el disco.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="renovacion_' . $poliza->numero_poliza . '.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'
        ]);
    }

    /**
     * Firma interactiva del PDF de renovación (Asesor Prefectura)
     */
    public function firmarRenovacion(Request $request, Poliza $poliza, \App\Services\SignPdfService $signService)
    {
        $request->validate([
            'password_certificado' => 'required|string',
            'sig_x' => 'nullable|numeric',
            'sig_y' => 'nullable|numeric',
            'sig_page' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        if (!$user->hasAnyRole(['Asesor Prefectura', 'Administrador', 'Super Admin'])) {
            return response()->json(['message' => 'No tienes permisos para firmar renovaciones.'], 403);
        }

        $renovacion = \App\Models\PolizaRenovacion::where('poliza_nueva_id', $poliza->id)->firstOrFail();

        if (!$renovacion->archivo_renovacion) {
            return response()->json(['message' => 'No hay documento de renovación para firmar.'], 400);
        }

        if (!$user->certificado_path) {
            return response()->json(['message' => 'No tiene un certificado configurado en su perfil.'], 400);
        }

        $pathRaw = storage_path('app/public/' . $renovacion->archivo_renovacion);
        if (!file_exists($pathRaw)) {
            return response()->json(['message' => 'El PDF de renovación no existe.'], 404);
        }

        $pdfContent = file_get_contents($pathRaw);
        $rolesFirmante = $user->roles->pluck('name')->toArray();

        try {
            $pdfFirmado = $signService->signPdfString(
                pdfContent: $pdfContent,
                certificadoPath: $user->certificado_path,
                password: $request->password_certificado,
                nombreFirmante: $user->name,
                rolesFirmante: $rolesFirmante,
                motivo: 'Firma de Renovación de Póliza',
                sigX: $request->sig_x ? floatval($request->sig_x) : null,
                sigY: $request->sig_y ? floatval($request->sig_y) : null,
                sigPage: $request->sig_page ? intval($request->sig_page) : null
            );

            // Reemplazar el archivo original con el firmado
            file_put_contents($pathRaw, $pdfFirmado);

            // Marcar como firmado
            $renovacion->update([
                'estado_firma_asesor' => true
            ]);

            // Devolver el documento firmado para que el navegador lo descargue
            return response($pdfFirmado)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="Renovacion_' . $poliza->numero_poliza . '_Firmada.pdf"');


        } catch (\Exception $e) {
            // Manejar errores de firma igual que en los oficios base
            if (strpos($e->getMessage(), 'Contraseña') !== false) {
                return response()->json(['message' => 'Contraseña del certificado incorrecta. Revise e intente de nuevo.'], 422);
            }
            \Illuminate\Support\Facades\Log::error('Error firmando renovación: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => 'Ocurrió un error al firmar el PDF criptográficamente.'], 500);
        }
    }

    /**
     * Enviar el oficio firmado por email a la aseguradora (máximo 2 envíos)
     */
    public function enviarOficio(Request $request, Poliza $poliza)
    {
        $request->validate([
            'to' => 'required|array|min:1',
            'to.*' => 'email',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'asunto' => 'required|string|max:255',
            'cuerpo' => 'required|string',
        ]);

        if (!$poliza->oficio_path) {
            return response()->json(['message' => 'Esta póliza no tiene un oficio generado.'], 400);
        }

        if (!$poliza->oficio_firmado_gestor || !$poliza->oficio_firmado_tesorero) {
            return response()->json(['message' => 'El oficio debe estar firmado por ambas partes antes de enviarlo.'], 400);
        }

        // Determinar cuál envío corresponde
        if ($poliza->oficio_email_1_at && $poliza->oficio_email_2_at) {
            return response()->json(['message' => 'Ya se enviaron los dos avisos para esta póliza. No se permiten más envíos.'], 400);
        }

        $esSegundoEnvio = !is_null($poliza->oficio_email_1_at);

        try {
            $mailable = new OficioMailable(
                poliza: $poliza,
                asunto: $request->asunto,
                cuerpo: $request->cuerpo,
            );

            $mailer = Mail::to($request->to);
            if (!empty($request->cc)) {
                $mailer->cc($request->cc);
            }
            $mailer->send($mailable);

            // Registrar el timestamp del envío correspondiente
            if ($esSegundoEnvio) {
                $poliza->update(['oficio_email_2_at' => now()]);
                $mensaje = 'Segundo aviso enviado exitosamente.';
            } else {
                $poliza->update(['oficio_email_1_at' => now()]);
                $mensaje = 'Primer aviso enviado exitosamente.';
            }

            return response()->json(['message' => $mensaje]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando oficio por email: ' . $e->getMessage());
            return response()->json(['message' => 'Error al enviar el correo: ' . $e->getMessage()], 500);
        }
    }
}
