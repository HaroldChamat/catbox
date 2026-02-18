<?php

namespace App\Notifications;

use App\Models\Devolucion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DevolucionResueltaNotification extends Notification
{
    use Queueable;

    public function __construct(public Devolucion $devolucion) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $aprobada = $this->devolucion->estaAprobada();
        
        return [
            'titulo'  => $aprobada ? '✅ Devolución Aprobada' : '❌ Devolución Rechazada',
            'mensaje' => $aprobada 
                ? "Tu solicitud de devolución fue aprobada. Se generó un crédito de $" . number_format($this->devolucion->monto_total, 0, ',', '.') 
                : "Tu solicitud de devolución fue rechazada.",
            'devolucion_id' => $this->devolucion->id,
            'orden_id' => $this->devolucion->orden_id,
            'tipo' => 'devolucion',
            'aprobada' => $aprobada,
        ];
    }
}