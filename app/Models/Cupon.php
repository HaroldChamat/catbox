<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cupon extends Model
{
    protected $table = 'cupones';

    protected $fillable = [
        'codigo', 'tipo', 'valor', 'alcance',
        'categoria_id', 'activo', 'limite_usos',
        'usos_actuales', 'fecha_expiracion',
    ];

    protected $casts = [
        'fecha_expiracion' => 'datetime',
        'activo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'cupon_producto');
    }

    public function usos()
    {
        return $this->hasMany(CuponUso::class);
    }

    // Generar código aleatorio único
    public static function generarCodigo($prefijo = 'CAT'): string
    {
        do {
            $codigo = $prefijo . '-' . strtoupper(Str::random(6));
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }

    // Verificar si el cupón es válido
    public function esValido(): bool
    {
        if (!$this->activo) return false;
        if ($this->fecha_expiracion && $this->fecha_expiracion->isPast()) return false;
        if ($this->limite_usos && $this->usos_actuales >= $this->limite_usos) return false;
        return true;
    }

    // Verificar si aplica a un producto específico
    public function aplicaA(Producto $producto): bool
    {
        if ($this->alcance === 'tienda') return true;
        if ($this->alcance === 'categoria') {
            return $this->categoria_id === $producto->categoria_id;
        }
        if ($this->alcance === 'productos') {
            return $this->productos->contains($producto->id);
        }
        return false;
    }

    // Calcular descuento sobre un monto
    public function calcularDescuento(float $subtotal): float
    {
        if ($this->tipo === 'porcentaje') {
            return round($subtotal * ($this->valor / 100), 2);
        }
        return min($this->valor, $subtotal);
    }
}