<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email}';
    protected $description = 'Envia un correo de prueba para verificar SMTP';

    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Enviando correo de prueba a: {$email}...");

        try {
            \Illuminate\Support\Facades\Mail::raw('Este es un correo de prueba del Sistema de Pólizas. Si lees esto, la configuración SMTP funciona correctamente.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Prueba de Configuración SMTP - Sistema Pólizas');
            });

            $this->info('¡Correo enviado con éxito! Revisa tu bandeja de entrada (y spam).');
        } catch (\Exception $e) {
            $this->error('Error al enviar el correo: ' . $e->getMessage());
        }
    }
}
