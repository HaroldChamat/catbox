<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Orden extends Model
{
    use HasFactory;

    protected $table = 'ordenes';

    protected $fillable = [
        'user_id',
        'numero_orden',
        'total',
        'estado',
        'fecha_entrega_estimada',
        'direccion_id',
        'notas',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha_entrega_estimada' => 'datetime',
    ];

    /**
     * Generar número de orden único al crear
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orden) {
            if (empty($orden->numero_orden)) {
                $orden->numero_orden = 'ORD-' . strtoupper(Str::random(10));
            }
        });
    }

    /**
     * Relación: Una orden pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Una orden tiene muchos detalles
     */
    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'orden_id');
    }

    /**
     * Relación: Una orden tiene un pago
     */
    public function pago()
    {
        return $this->hasOne(Pago::class, 'orden_id');
    }

    /**
     * Relación: Una orden tiene una dirección de entrega
     */
    public function direccion()
    {
        return $this->belongsTo(DireccionEntrega::class, 'direccion_id');
    }

    /**
     * Calcular el total de la orden desde sus detalles
     */
    public function calcularTotal()
    {
        return $this->detalles->sum('subtotal');
    }

    /**
     * Verificar si la orden puede ser editada
     */
    public function puedeEditarse()
    {
        return in_array($this->estado, ['pendiente', 'procesando']);
    }

    /**
    * Relación: Una orden tiene muchos comentarios
    */
    public function comentarios()
    {
        return $this->hasMany(ComentarioOrden::class, 'orden_id')->orderBy('created_at', 'asc');
    }

    /**
     * Obtener comentarios no leídos para el usuario actual
     */
    public function comentariosNoLeidosPara($esAdmin = false)
    {
        return $this->comentarios()
            ->where('leido', false)
            ->where('es_admin', '!=', $esAdmin)
            ->count();
    }
}