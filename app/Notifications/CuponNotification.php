<?php

namespace App\Notifications;

use App\Models\Cupon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CuponNotification extends Notification
{
    use Queueable;

    public function __construct(public Cupon $cupon) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $descripcion = $this->cupon->tipo === 'porcentaje'
            ? "{$this->cupon->valor}% de descuento"
            : "$" . number_format($this->cupon->valor, 0, ',', '.') . " de descuento";

        return (new MailMessage)
            ->subject('🎁 ¡Tienes un cupón de descuento en Catbox!')
            ->greeting("¡Hola {$notifiable->name}!")
            ->line("Tenemos un regalo para ti: **{$descripcion}**")
            ->line("Tu código de cupón es: **{$this->cupon->codigo}**")
            ->line($this->cupon->fecha_expiracion
                ? "Válido hasta: {$this->cupon->fecha_expiracion->format('d/m/Y')}"
                : "Sin fecha de expiración")
            ->action('Ir a la tienda', url('/productos'))
            ->line('¡Aplícalo en el carrito al momento de comprar!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'titulo'  => '🎁 ¡Tienes un cupón de descuento!',
            'mensaje' => "Usa el código {$this->cupon->codigo} en tu próxima compra.",
            'codigo'  => $this->cupon->codigo,
            'tipo'    => $this->cupon->tipo,
            'valor'   => $this->cupon->valor,
        ];
    }
}