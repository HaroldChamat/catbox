<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'orden_id',
        'metodo_pago',
        'monto',
        'estado',
        'transaction_id',
        'datos_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'datos_pago' => 'array',
    ];

    /**
     * Relación: Un pago pertenece a una orden
     */
    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }

    /**
     * Verificar si el pago fue completado
     */
    public function estaCompletado()
    {
        return $this->estado === 'completado';
    }

    /**
     * Verificar si el pago está pendiente
     */
    public function estaPendiente()
    {
        return $this->estado === 'pendiente';
    }
}