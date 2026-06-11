<?php

namespace App\Http\Controllers;

use App\Models\Aseguradora;
use App\Models\Ciudad;
use App\Models\SucursalAseguradora;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AseguradoraController extends Controller
{
    public function index()
    {
        return Inertia::render('Aseguradoras/Index', [
            'aseguradoras' => Aseguradora::with('sucursales.ciudad')
                ->withCount('sucursales')
                ->latest()
                ->get(),
            'ciudades' => Ciudad::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_empresa' => 'required|string|max:150',
        ]);

        $validated['activo'] = true;
        Aseguradora::create($validated);

        return redirect()->back()->with('message', 'Aseguradora registrada.');
    }

    public function update(Request $request, Aseguradora $aseguradora)
    {
        $validated = $request->validate([
            'nombre_empresa' => 'required|string|max:150',
            'activo' => 'boolean',
        ]);

        $aseguradora->update($validated);

        return redirect()->back()->with('message', 'Aseguradora actualizada.');
    }

    public function destroy(Aseguradora $aseguradora)
    {
        $aseguradora->delete();
        return redirect()->back()->with('message', 'Aseguradora eliminada.');
    }

    // ─── Sucursales ───────────────────────────────────────────────────────────

    public function storeSucursal(Request $request, Aseguradora $aseguradora)
    {
        $validated = $request->validate([
            'ciudad_id'       => 'nullable|exists:ciudades,id',
            'nombre_contacto' => 'nullable|string|max:100',
            'correo1'         => 'nullable|email|max:100',
            'correo2'         => 'nullable|email|max:100',
            'correo3'         => 'nullable|email|max:100',
            'correo4'         => 'nullable|email|max:100',
            'correo5'         => 'nullable|email|max:100',
            'correo6'         => 'nullable|email|max:100',
            'celular1'        => 'nullable|string|max:15',
            'celular2'        => 'nullable|string|max:15',
            'celular3'        => 'nullable|string|max:15',
            'telefono_fijo1'  => 'nullable|string|max:15',
            'telefono_fijo2'  => 'nullable|string|max:15',
            'extensiones'     => 'nullable|string|max:50',
        ]);

        $aseguradora->sucursales()->create($validated);

        return redirect()->back()->with('message', 'Sucursal agregada.');
    }

    public function updateSucursal(Request $request, Aseguradora $aseguradora, SucursalAseguradora $sucursal)
    {
        abort_if($sucursal->aseguradora_id !== $aseguradora->id, 403);

        $validated = $request->validate([
            'ciudad_id'       => 'nullable|exists:ciudades,id',
            'nombre_contacto' => 'nullable|string|max:100',
            'correo1'         => 'nullable|email|max:100',
            'correo2'         => 'nullable|email|max:100',
            'correo3'         => 'nullable|email|max:100',
            'correo4'         => 'nullable|email|max:100',
            'correo5'         => 'nullable|email|max:100',
            'correo6'         => 'nullable|email|max:100',
            'celular1'        => 'nullable|string|max:15',
            'celular2'        => 'nullable|string|max:15',
            'celular3'        => 'nullable|string|max:15',
            'telefono_fijo1'  => 'nullable|string|max:15',
            'telefono_fijo2'  => 'nullable|string|max:15',
            'extensiones'     => 'nullable|string|max:50',
        ]);

        $sucursal->update($validated);

        return redirect()->back()->with('message', 'Sucursal actualizada.');
    }

    public function destroySucursal(Aseguradora $aseguradora, SucursalAseguradora $sucursal)
    {
        abort_if($sucursal->aseguradora_id !== $aseguradora->id, 403);

        $sucursal->delete();

        return redirect()->back()->with('message', 'Sucursal eliminada.');
    }
}
