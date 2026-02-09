<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaProducto;
use App\Models\Orden;
use App\Models\User;
use App\Models\Estadistica;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', function ($request, $next) {
            if (!auth()->user()->esAdmin()) {
                abort(403, 'No tienes permisos de administrador');
            }
            return $next($request);
        }]);
    }

    /**
     * Dashboard principal del admin
     */
    public function index()
    {
        $stats = [
            'total_productos' => Producto::count(),
            'total_ordenes' => Orden::count(),
            'total_usuarios' => User::where('is_admin', false)->count(),
            'ventas_mes' => Orden::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'ordenes_pendientes' => Orden::where('estado', 'pendiente')->count(),
            'productos_bajo_stock' => Producto::where('stock', '<', 10)->count(),
        ];

        // Órdenes recientes
        $ordenesRecientes = Orden::with(['user', 'detalles'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top 5 productos más vendidos
        $topProductos = Estadistica::topProductosMasVendidos(5);

        return view('admin.dashboard', compact('stats', 'ordenesRecientes', 'topProductos'));
    }
}