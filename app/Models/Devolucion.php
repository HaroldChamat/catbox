<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    protected $table = 'devoluciones';

    protected $fillable = [
        'orden_id', 'user_id', 'estado', 'motivo',
        'respuesta_admin', 'monto_total',
        'fecha_aprobacion', 'fecha_rechazo',
    ];

    protected $casts = [
        'fecha_aprobacion' => 'datetime',
        'fecha_rechazo' => 'datetime',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(DevolucionItem::class);
    }

    public function credito()
    {
        return $this->hasOne(Credito::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    public function estaPendiente()
    {
        return $this->estado === 'pendiente';
    }

    public function estaAprobada()
    {
        return $this->estado === 'aprobada';
    }
}