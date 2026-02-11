<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaProducto;
use App\Models\Orden;
use App\Models\DetalleOrden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadisticaController extends Controller
{
    public function index(Request $request)
    {
        $categorias = CategoriaProducto::all();
        
        // Filtros
        $categoriaId = $request->get('categoria_id');
        $productoId = $request->get('producto_id');
        $fechaDesde = $request->get('fecha_desde', now()->subMonths(3)->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));

        // Productos más vendidos
        $productosVendidosQuery = DetalleOrden::select(
                'producto_id',
                DB::raw('SUM(cantidad) as total_vendido'),
                DB::raw('SUM(subtotal) as total_ingresos')
            )
            ->with('producto.categoria')
            ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido');

        if ($categoriaId) {
            $productosVendidosQuery->whereHas('producto', function($q) use ($categoriaId) {
                $q->where('categoria_id', $categoriaId);
            });
        }

        if ($productoId) {
            $productosVendidosQuery->where('producto_id', $productoId);
        }

        $productosVendidos = $productosVendidosQuery->limit(10)->get();

        // Ventas por categoría
        $ventasPorCategoria = DetalleOrden::select(
                'productos.categoria_id',
                DB::raw('SUM(detalles_orden.cantidad) as total_vendido'),
                DB::raw('SUM(detalles_orden.subtotal) as total_ingresos')
            )
            ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
            ->whereBetween('detalles_orden.created_at', [$fechaDesde, $fechaHasta])
            ->groupBy('productos.categoria_id')
            ->with('producto.categoria')
            ->get();

        // Ventas por día (últimos 30 días para la gráfica)
        $ventasPorDia = Orden::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as ordenes'),
                DB::raw('SUM(total) as ingresos')
            )
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->whereIn('estado', ['procesando', 'enviado', 'entregado'])
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();

        // Productos para el filtro de categoría
        $productos = [];
        if ($categoriaId) {
            $productos = Producto::where('categoria_id', $categoriaId)
                ->orderBy('nombre')
                ->get();
        }

        // Estadísticas generales
        $stats = [
            'total_ventas' => Orden::whereIn('estado', ['procesando', 'enviado', 'entregado'])
                ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
                ->sum('total'),
            'total_ordenes' => Orden::whereBetween('created_at', [$fechaDesde, $fechaHasta])->count(),
            'productos_vendidos' => DetalleOrden::whereBetween('created_at', [$fechaDesde, $fechaHasta])
                ->sum('cantidad'),
            'ticket_promedio' => Orden::whereIn('estado', ['procesando', 'enviado', 'entregado'])
                ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
                ->avg('total'),
        ];

        return view('admin.estadisticas.index', compact(
            'categorias',
            'productosVendidos',
            'ventasPorCategoria',
            'ventasPorDia',
            'productos',
            'stats',
            'categoriaId',
            'productoId',
            'fechaDesde',
            'fechaHasta'
        ));
    }

    public function ventasRealTime()
    {
        // Endpoint para actualizar las ventas en tiempo real via AJAX
        $ventasHoy = Orden::whereDate('created_at', today())
            ->whereIn('estado', ['procesando', 'enviado', 'entregado'])
            ->sum('total');

        $ordenesHoy = Orden::whereDate('created_at', today())->count();

        return response()->json([
            'ventas_hoy' => $ventasHoy,
            'ordenes_hoy' => $ordenesHoy,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}