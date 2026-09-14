<?php

namespace App\Providers;

use App\Mail\Transport\GmailApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Política de contraseñas aplicada en todos los formularios (login, cambio
        // obligatorio, invitación de cuenta, "olvidé mi contraseña", etc.), ya que
        // todos validan con Password::defaults().
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        Mail::extend('gmailapi', function (array $config) {
            return new GmailApiTransport(
                $config['service_account_path'] ?? '',
                $config['impersonate'] ?? '',
            );
        });

        // Forzar HTTPS y detectar URL raíz dinámicamente para soportar Dominio e IP simultáneamente
        if (isset($_SERVER['HTTP_HOST'])) {
            $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
                       || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            
            $protocol = $isHttps ? 'https' : 'http';
            
            // Forzamos el esquema y la URL raíz basada en la petición actual
            if ($isHttps) {
                URL::forceScheme('https');
            }
            URL::forceRootUrl($protocol . '://' . $_SERVER['HTTP_HOST']);
        }

        Vite::prefetch(concurrency: 3);
    }
}
