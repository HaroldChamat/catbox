<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'slug',  // ← Agregado
        'precio',
        'stock',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'stock' => 'integer',
    ];

    // SIN BOOT METHOD - Sin eventos que puedan interferir
    
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

    public function favoritos()
    {
        return $this->hasMany(Favorito::class);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }

    public function resenasAprobadas()
    {
        return $this->hasMany(Resena::class)->where('estado', 'aprobada');
    }

    public function promedioCalificacion()
    {
        return $this->resenasAprobadas()->avg('calificacion') ?? 0;
    }

    public function esFavoritoDeUsuario($userId)
    {
        return $this->favoritos()->where('user_id', $userId)->exists();
    }
}