<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevolucionItem extends Model
{
    protected $table = 'devolucion_items';

    protected $fillable = [
        'devolucion_id', 'detalle_orden_id', 'cantidad', 'monto',
    ];

    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class);
    }

    public function detalleOrden()
    {
        return $this->belongsTo(DetalleOrden::class);
    }
}