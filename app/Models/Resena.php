<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'resenas';
    protected $fillable = ['user_id', 'producto_id', 'calificacion', 'comentario', 'estado'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function estrellas()
    {
        return str_repeat('★', $this->calificacion) . str_repeat('☆', 5 - $this->calificacion);
    }
}