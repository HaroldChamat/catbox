<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DireccionEntrega extends Model
{
    use HasFactory;

    protected $table = 'direcciones_entrega';

    protected $fillable = [
        'user_id',
        'direccion',
        'ciudad',
        'codigo_postal',
        'telefono',
        'latitud',
        'longitud',
        'es_principal',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'es_principal' => 'boolean',
    ];

    /**
     * Relación: Una dirección pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtener la dirección completa como string
     */
    public function getDireccionCompletaAttribute()
    {
        return "{$this->direccion}, {$this->ciudad}, {$this->codigo_postal}";
    }

    /**
     * Establecer como dirección principal
     */
    public function establecerComoPrincipal()
    {
        // Quitar el flag de principal a todas las otras direcciones del usuario
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['es_principal' => false]);

        // Establecer esta como principal
        $this->es_principal = true;
        $this->save();
    }
}