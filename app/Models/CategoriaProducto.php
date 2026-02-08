<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaProducto extends Model
{
    use HasFactory;

    protected $table = 'categorias_producto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'slug',
    ];

    /**
     * Relación: Una categoría tiene muchos productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    /**
     * Relación: Una categoría tiene muchas estadísticas
     */
    public function estadisticas()
    {
        return $this->hasMany(Estadistica::class, 'categoria_id');
    }
}