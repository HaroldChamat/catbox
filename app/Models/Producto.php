<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'activo',
        'slug',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'stock' => 'integer',
    ];

    /**
     * Boot del modelo con logging para detectar eliminaciones
     */
    protected static function boot()
    {
        parent::boot();

        // Log cuando se intente eliminar un producto
        static::deleting(function ($producto) {
            Log::warning('ALERTA: Intentando eliminar producto', [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
            ]);
        });

        // Log cuando se actualice un producto
        static::updating(function ($producto) {
            Log::info('Actualizando producto', [
                'id' => $producto->id,
                'cambios' => $producto->getDirty()
            ]);
        });
    }

    /**
     * Relación: Un producto pertenece a una categoría
     */
    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }

    /**
     * Relación: Un producto tiene muchas imágenes
     */
    public function imagenes()
    {
        return $this->hasMany(ImagenProducto::class, 'producto_id');
    }

    /**
     * Obtener la imagen principal del producto
     */
    public function imagenPrincipal()
    {
        return $this->hasOne(ImagenProducto::class, 'producto_id')->where('es_principal', true);
    }

    /**
     * Relación: Un producto puede estar en muchos items de carrito
     */
    public function itemsCarrito()
    {
        return $this->hasMany(ItemCarrito::class, 'producto_id');
    }

    /**
     * Relación: Un producto puede estar en muchos detalles de orden
     */
    public function detallesOrden()
    {
        return $this->hasMany(DetalleOrden::class, 'producto_id');
    }

    /**
     * Relación: Un producto tiene muchas estadísticas
     */
    public function estadisticas()
    {
        return $this->hasMany(Estadistica::class, 'producto_id');
    }
}