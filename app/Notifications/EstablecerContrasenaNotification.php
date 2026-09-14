<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Reemplaza la notificación de reset de contraseña por defecto de Laravel.
 * Se usa tanto para invitar a un usuario recién creado (establece su clave
 * por primera vez) como para el "olvidé mi contraseña" de autoservicio.
 * Se envía por el mailer 'sistema' (Gmail API), separado del SMTP que usa
 * el gestor para enviar oficios.
 */
class EstablecerContrasenaNotification extends Notification
{
    public function __construct(private readonly string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $minutos = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->mailer('sistema')
            ->subject('Establece tu contraseña - Sistema de Pólizas GADPC')
            ->greeting("Hola {$notifiable->name},")
            ->line('Ingresa al siguiente enlace para establecer tu contraseña de acceso al Sistema de Pólizas del GADPC.')
            ->action('Establecer mi contraseña', $url)
            ->line("Este enlace expirará en {$minutos} minutos.")
            ->line('Si no esperabas este correo, puedes ignorarlo con confianza.');
    }
}
