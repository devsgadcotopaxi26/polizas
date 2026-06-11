<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdministradorController extends Controller
{
    public function index()
    {
        return Inertia::render('Administradores/Index', [
            'administradores' => Administrador::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        Administrador::create($validated);

        return redirect()->back()->with('message', 'Administrador registrado.');
    }

    public function update(Request $request, Administrador $administrador)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'activo' => 'boolean',
        ]);

        $administrador->update($validated);

        return redirect()->back()->with('message', 'Administrador actualizado.');
    }

    public function destroy(Administrador $administrador)
    {
        $administrador->delete();
        return redirect()->back()->with('message', 'Administrador eliminado.');
    }
}
