<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class PasswordChangeController extends Controller
{
    /**
     * Muestra el formulario de cambio de contraseña obligatorio.
     */
    public function show()
    {
        return Inertia::render('Auth/ChangePassword');
    }

    /**
     * Procesa el cambio de contraseña y libera el flag.
     */
    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')->with('message', '¡Contraseña actualizada exitosamente! Bienvenido al sistema.');
    }
}
