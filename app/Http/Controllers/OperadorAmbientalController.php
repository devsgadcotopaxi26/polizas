<?php

namespace App\Http\Controllers;

use App\Models\OperadorAmbiental;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperadorAmbientalController extends Controller
{
    public function index()
    {
        return Inertia::render('OperadoresAmbientales/Index', [
            'operadores' => OperadorAmbiental::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'        => 'required|string|max:75',
            'empresa'       => 'nullable|string|max:150',
            'correo'        => 'nullable|email|max:50|unique:operadores_ambientales',
            'celular'       => 'nullable|string|max:10',
            'telefono_fijo' => 'nullable|string|max:15',
            'taxid'         => 'nullable|string|max:15|unique:operadores_ambientales',
        ]);

        OperadorAmbiental::create($validated);

        return redirect()->back()->with('message', 'Operador ambiental registrado.');
    }

    public function update(Request $request, OperadorAmbiental $operadorAmbiental)
    {
        $validated = $request->validate([
            'nombre'        => 'required|string|max:75',
            'empresa'       => 'nullable|string|max:150',
            'correo'        => 'nullable|email|max:50|unique:operadores_ambientales,correo,' . $operadorAmbiental->id,
            'celular'       => 'nullable|string|max:10',
            'telefono_fijo' => 'nullable|string|max:15',
            'taxid'         => 'nullable|string|max:15|unique:operadores_ambientales,taxid,' . $operadorAmbiental->id,
        ]);

        $operadorAmbiental->update($validated);

        return redirect()->back()->with('message', 'Operador ambiental actualizado.');
    }

    public function destroy(OperadorAmbiental $operadorAmbiental)
    {
        $operadorAmbiental->delete();
        return redirect()->back()->with('message', 'Operador ambiental eliminado.');
    }
}
