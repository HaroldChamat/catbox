<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',       
        'telefono',     
        'fecha_nacimiento', 
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Relación: Un usuario tiene un carrito
     */
    public function carrito()
    {
        return $this->hasOne(Carrito::class, 'user_id');
    }

    /**
     * Relación: Un usuario tiene muchas órdenes
     */
    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'user_id');
    }

    /**
     * Relación: Un usuario tiene muchas direcciones de entrega
     */
    public function direcciones()
    {
        return $this->hasMany(DireccionEntrega::class, 'user_id');
    }

    /**
     * Obtener la dirección principal del usuario
     */
    public function direccionPrincipal()
    {
        return $this->hasOne(DireccionEntrega::class, 'user_id')->where('es_principal', true);
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function esAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Obtener o crear un carrito para el usuario
     */
    public function obtenerOCrearCarrito()
    {
        if (!$this->carrito) {
            return $this->carrito()->create();
        }
        return $this->carrito;
    }

    public function devoluciones()
    {
        return $this->hasMany(Devolucion::class);
    }

    public function creditos()
    {
        return $this->hasMany(Credito::class);
    }

    public function creditosDisponibles()
    {
        return $this->creditos()->where('saldo', '>', 0)->where('usado', false)->get();
    }

    public function saldoCreditosTotal()
    {
        return $this->creditos()->where('usado', false)->sum('saldo');
    }
}