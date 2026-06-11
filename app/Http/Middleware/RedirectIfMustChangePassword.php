<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfMustChangePassword
{
    /**
     * Rutas permitidas aunque el usuario deba cambiar su contraseña.
     */
    protected array $except = [
        'password.change',
        'password.change.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            // Permitir las rutas exentas (cambio de contraseña y logout)
            if (!in_array($request->route()?->getName(), $this->except)) {
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
