<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CiudadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ciudades = Ciudad::orderBy('nombre')->get();
        return Inertia::render('Ciudades/Index', [
            'ciudades' => $ciudades,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:ciudades,nombre',
        ]);

        Ciudad::create([
            'nombre' => $request->nombre,
        ]);

        return redirect()->back()->with('message', 'Ciudad creada exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ciudad $ciudad)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:ciudades,nombre,' . $ciudad->id,
        ]);

        $ciudad->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->back()->with('message', 'Ciudad actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ciudad $ciudad)
    {
        // Verificar si la ciudad tiene aseguradoras asociadas
        if ($ciudad->aseguradoras()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar la ciudad porque tiene aseguradoras asociadas.');
        }

        $ciudad->delete();

        return redirect()->back()->with('message', 'Ciudad eliminada exitosamente.');
    }
}
