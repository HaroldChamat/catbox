<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComentarioOrden extends Model
{
    use HasFactory;

    protected $table = 'comentarios_orden';

    protected $fillable = [
        'orden_id',
        'user_id',
        'comentario',
        'es_admin',
        'leido'
    ];

    protected $casts = [
        'es_admin' => 'boolean',
        'leido' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con la orden
     */
    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }

    /**
     * Relación con el usuario que escribió el comentario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para obtener solo comentarios no leídos
     */
    public function scopeNoLeidos($query)
    {
        return $query->where('leido', false);
    }

    /**
     * Scope para obtener comentarios de admin
     */
    public function scopeDeAdmin($query)
    {
        return $query->where('es_admin', true);
    }

    /**
     * Scope para obtener comentarios de clientes
     */
    public function scopeDeCliente($query)
    {
        return $query->where('es_admin', false);
    }

    /**
     * Scope para obtener comentarios ordenados por fecha (más recientes primero)
     */
    public function scopeMasRecientes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope para obtener comentarios de una orden específica
     */
    public function scopeDeOrden($query, $ordenId)
    {
        return $query->where('orden_id', $ordenId);
    }

    /**
     * Marcar como leído
     */
    public function marcarComoLeido()
    {
        $this->update(['leido' => true]);
    }

    /**
     * Verificar si el comentario pertenece a un admin
     */
    public function esDeAdmin()
    {
        return $this->es_admin;
    }

    /**
     * Verificar si el comentario ha sido leído
     */
    public function estaLeido()
    {
        return $this->leido;
    }

    /**
     * Obtener el nombre del autor del comentario
     */
    public function getNombreAutorAttribute()
    {
        return $this->user ? $this->user->name : 'Usuario desconocido';
    }

    /**
     * Obtener el tiempo transcurrido desde la creación del comentario
     */
    public function getTiempoTranscurridoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}