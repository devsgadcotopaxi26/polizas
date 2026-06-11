<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
