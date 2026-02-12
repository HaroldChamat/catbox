<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Orden;
use App\Models\User;
use App\Models\DetalleOrden;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // Estadísticas generales
        $stats = [
            'total_productos' => Producto::count(),
            'total_ordenes' => Orden::count(),
            'total_usuarios' => User::where('is_admin', false)->count(),
            'ventas_mes' => Orden::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereIn('estado', ['procesando', 'enviado', 'entregado'])
                ->sum('total'),
            'ordenes_pendientes' => Orden::where('estado', 'pendiente')->count(),
            'productos_bajo_stock' => Producto::where('stock', '<', 10)->count(),
        ];

        // Órdenes recientes
        $ordenesRecientes = Orden::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top 5 productos más vendidos
        $topProductos = DetalleOrden::select(
                'producto_id',
                DB::raw('SUM(cantidad) as total_vendido')
            )
            ->with('producto')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'ordenesRecientes', 'topProductos'));
    }
}