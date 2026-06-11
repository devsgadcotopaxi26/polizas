<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Contratista;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContratoController extends Controller
{
    public function index()
    {
        return Inertia::render('Contratos/Index', [
            'contratos' => Contrato::with(['contratista', 'administrador'])->latest()->get(),
            'contratistas' => Contratista::orderBy('nombre_cont')->get(),
            'administradores' => Administrador::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $messages = [
            'numero_contrato.required' => 'Por favor ingrese el N° de Contrato.',
            'numero_contrato.unique' => 'Este número de contrato ya se encuentra registrado preventivamente.',
            'contratista_id.required' => 'Debe seleccionar un contratista de la lista.',
        ];

        $validated = $request->validate([
            'numero_contrato' => 'required|string|max:100|unique:contratos',
            'objeto_contratacion' => 'nullable|string',
            'valor_contrato' => 'nullable|numeric|min:0',
            'anticipo' => 'nullable|numeric|min:0',
            'contratista_id' => 'required|exists:contratistas,id',
            'administrador_id' => 'nullable|exists:administradores,id',
        ], $messages);

        Contrato::create($validated);

        return redirect()->back()->with('message', 'Contrato registrado.');
    }

    public function update(Request $request, Contrato $contrato)
    {
        $messages = [
            'numero_contrato.required' => 'Por favor ingrese el N° de Contrato.',
            'numero_contrato.unique' => 'Ya existe otro contrato registrado con este número.',
            'contratista_id.required' => 'Debe seleccionar un contratista de la lista.',
        ];

        $validated = $request->validate([
            'numero_contrato' => 'required|string|max:100|unique:contratos,numero_contrato,' . $contrato->id,
            'objeto_contratacion' => 'nullable|string',
            'valor_contrato' => 'nullable|numeric|min:0',
            'anticipo' => 'nullable|numeric|min:0',
            'contratista_id' => 'required|exists:contratistas,id',
            'administrador_id' => 'nullable|exists:administradores,id',
        ], $messages);

        $contrato->update($validated);

        return redirect()->back()->with('message', 'Contrato actualizado.');
    }

    public function destroy(Contrato $contrato)
    {
        $contrato->delete();
        return redirect()->back()->with('message', 'Contrato eliminado.');
    }
}
