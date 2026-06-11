<?php

namespace App\Notifications;

use App\Models\Poliza;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PolizaVencimientoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $poliza;
    protected $dias;

    /**
     * Create a new notification instance.
     */
    public function __construct(Poliza $poliza, $dias)
    {
        $this->poliza = $poliza;
        $this->dias = $dias;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $urgencia = $this->dias <= 15 ? 'CRÍTICA' : 'IMPORTANTE';

        return (new MailMessage)
            ->subject("Alerta $urgencia: Vencimiento de Póliza #" . $this->poliza->numero_poliza)
            ->greeting("Hola, " . $notifiable->name)
            ->line("Le informamos que la siguiente póliza está próxima a vencer en " . $this->dias . " días.")
            ->line("**Número de Póliza:** " . $this->poliza->numero_poliza)
            ->line("**Categoría:** " . $this->poliza->categoria_poliza)
            ->line("**Subtipo:** " . $this->poliza->subtipo_poliza)
            ->line("**Fecha de Vencimiento:** " . \Carbon\Carbon::parse($this->poliza->fecha_vencimiento)->format('d/m/Y'))
            ->line("**Aseguradora:** " . ($this->poliza->sucursal->aseguradora->nombre_empresa ?? 'N/A'))
            ->action('Ver Póliza en el Sistema', url('/polizas/' . $this->poliza->id))
            ->line('Por favor, tome las medidas necesarias para la renovación oportuna.')
            ->salutation('Atentamente, Sistema de Control de Pólizas');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'poliza_id' => $this->poliza->id,
            'numero_poliza' => $this->poliza->numero_poliza,
            'dias_para_vencer' => $this->dias,
            'mensaje' => "La póliza #{$this->poliza->numero_poliza} vence en {$this->dias} días.",
        ];
    }
}
