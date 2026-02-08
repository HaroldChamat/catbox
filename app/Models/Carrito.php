<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carritos';

    protected $fillable = [
        'user_id',
    ];

    /**
     * Relación: Un carrito pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un carrito tiene muchos items
     */
    public function items()
    {
        return $this->hasMany(ItemCarrito::class, 'carrito_id');
    }

    /**
     * Calcular el total del carrito
     */
    public function calcularTotal()
    {
        return $this->items->sum(function ($item) {
            return $item->cantidad * $item->precio_unitario;
        });
    }

    /**
     * Obtener el número total de productos en el carrito
     */
    public function totalProductos()
    {
        return $this->items->sum('cantidad');
    }
}