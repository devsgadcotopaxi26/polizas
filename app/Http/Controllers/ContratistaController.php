<?php

namespace App\Http\Controllers;

use App\Models\Contratista;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContratistaController extends Controller
{
    public function index()
    {
        return Inertia::render('Contratistas/Index', [
            'contratistas' => Contratista::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_cont' => 'required|string|max:75',
            'correo_cont' => 'nullable|email|max:50|unique:contratistas',
            'celular_cont' => 'nullable|string|max:10',
            'telefono_fijo' => 'nullable|string|max:15',
            'taxid' => 'nullable|string|max:15|unique:contratistas',
        ]);

        Contratista::create($validated);

        return redirect()->back()->with('message', 'Contratista registrado.');
    }

    public function update(Request $request, Contratista $contratista)
    {
        $validated = $request->validate([
            'nombre_cont' => 'required|string|max:75',
            'correo_cont' => 'nullable|email|max:50|unique:contratistas,correo_cont,' . $contratista->id,
            'celular_cont' => 'nullable|string|max:10',
            'telefono_fijo' => 'nullable|string|max:15',
            'taxid' => 'nullable|string|max:15|unique:contratistas,taxid,' . $contratista->id,
        ]);

        $contratista->update($validated);

        return redirect()->back()->with('message', 'Contratista actualizado.');
    }

    public function destroy(Contratista $contratista)
    {
        $contratista->delete();
        return redirect()->back()->with('message', 'Contratista eliminado.');
    }
}
