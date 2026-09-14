<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailSistema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-sistema {email}';
    protected $description = 'Envía un correo de prueba usando el mailer "sistema" (Gmail API)';

    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Enviando correo de prueba (mailer 'sistema') a: {$email}...");

        try {
            Mail::mailer('sistema')->raw(
                'Este es un correo de prueba del mailer "sistema" (Gmail API). Si lees esto, la configuración funciona correctamente.',
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Prueba mailer sistema - Sistema Pólizas')
                        ->from(config('mail.mailers.sistema.from_address'), config('mail.mailers.sistema.from_name'));
                }
            );

            $this->info('¡Correo enviado con éxito! Revisa la bandeja de entrada (y spam).');
        } catch (\Exception $e) {
            $this->error('Error al enviar el correo: ' . $e->getMessage());
        }
    }
}
