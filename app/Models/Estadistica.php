<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estadistica extends Model
{
    use HasFactory;

    protected $table = 'estadisticas';

    protected $fillable = [
        'producto_id',
        'categoria_id',
        'cantidad_vendida',
        'ingresos_generados',
        'fecha',
    ];

    protected $casts = [
        'ingresos_generados' => 'decimal:2',
        'fecha' => 'date',
    ];

    /**
     * Relación: Una estadística puede pertenecer a un producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Relación: Una estadística puede pertenecer a una categoría
     */
    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }

    /**
     * Obtener los top N productos más vendidos
     */
    public static function topProductosMasVendidos($limit = 5)
    {
        return self::with('producto')
            ->whereNotNull('producto_id')
            ->selectRaw('producto_id, SUM(cantidad_vendida) as total_vendido')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener las top N categorías más vendidas
     */
    public static function topCategoriasMasVendidas($limit = 5)
    {
        return self::with('categoria')
            ->whereNotNull('categoria_id')
            ->selectRaw('categoria_id, SUM(cantidad_vendida) as total_vendido, SUM(ingresos_generados) as total_ingresos')
            ->groupBy('categoria_id')
            ->orderByDesc('total_vendido')
            ->limit($limit)
            ->get();
    }
}