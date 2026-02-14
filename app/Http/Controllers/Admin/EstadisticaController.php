<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaProducto;
use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstadisticaController extends Controller
{
    // Dashboard principal
    public function index(Request $request)
    {
        $periodo = $request->get('periodo', '30');
        $categoriaId = $request->get('categoria_id');
        $fechaDesde = $request->get('fecha_desde', now()->subDays($periodo)->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        
        $diasPeriodo = Carbon::parse($fechaDesde)->diffInDays(Carbon::parse($fechaHasta));
        $fechaDesdePrevio = Carbon::parse($fechaDesde)->subDays($diasPeriodo)->format('Y-m-d');
        $fechaHastaPrevio = Carbon::parse($fechaDesde)->subDay()->format('Y-m-d');

        $categorias = CategoriaProducto::all();
        $stats = $this->getGeneralStats($fechaDesde, $fechaHasta, $categoriaId);
        $comparison = $this->getComparison($fechaDesde, $fechaHasta, $fechaDesdePrevio, $fechaHastaPrevio, $categoriaId);
        $topProductos = $this->getTopProductos($fechaDesde, $fechaHasta, $categoriaId, 10);
        $ventasPorDia = $this->getVentasPorDia($fechaDesde, $fechaHasta, $categoriaId);
        $distribucionEstados = $this->getDistribucionEstados($fechaDesde, $fechaHasta);
        
        // CORREGIDO: Asegurar que ventasPorCategoria siempre esté disponible
        $ventasPorCategoria = $this->getVentasPorCategoria($fechaDesde, $fechaHasta);
        
        return view('admin.estadisticas.index', compact(
            'categorias', 'stats', 'comparison', 'topProductos', 
            'ventasPorDia', 'distribucionEstados', 'ventasPorCategoria',
            'periodo', 'categoriaId', 'fechaDesde', 'fechaHasta'
        ));
    }
    
    // Análisis de ventas
    public function ventas(Request $request)
    {
        $periodo = $request->get('periodo', '30');
        $categoriaId = $request->get('categoria_id');
        $agrupar = $request->get('agrupar', 'dia');
        $fechaDesde = $request->get('fecha_desde', now()->subDays($periodo)->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        
        $categorias = CategoriaProducto::all();
        
        // Determinar qué datos cargar según el agrupamiento
        switch ($agrupar) {
            case 'hora':
                $ventasData = $this->getVentasPorHora($fechaDesde, $fechaHasta, $categoriaId);
                break;
            case 'categoria':
                $ventasData = $this->getVentasPorCategoria($fechaDesde, $fechaHasta);
                break;
            default:
                $ventasData = $this->getVentasPorDia($fechaDesde, $fechaHasta, $categoriaId);
        }
        
        $stats = $this->getGeneralStats($fechaDesde, $fechaHasta, $categoriaId);
        
        $ventasStats = [
            'total_ventas' => $stats['total_ventas'],
            'promedio_venta' => $stats['ticket_promedio'],
            'venta_mayor' => Orden::whereIn('estado', ['procesando', 'enviado', 'entregado'])
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->max('total') ?? 0,
            'total_ordenes' => $stats['total_ordenes'],
        ];
        
        // Siempre cargar estos datos para los gráficos de resumen
        $ventasPorCategoria = $this->getVentasPorCategoria($fechaDesde, $fechaHasta);
        $ventasPorHora = $this->getVentasPorHora($fechaDesde, $fechaHasta, $categoriaId);
        $ventasPorDia = $this->getVentasPorDia($fechaDesde, $fechaHasta, $categoriaId);
        
        return view('admin.estadisticas.ventas', compact(
            'categorias', 'ventasData', 'stats', 'ventasStats', 
            'ventasPorCategoria', 'ventasPorHora', 'ventasPorDia',
            'periodo', 'categoriaId', 'agrupar', 'fechaDesde', 'fechaHasta'
        ));
    }
    
    // Análisis de productos
    public function productos(Request $request)
    {
        $periodo = $request->get('periodo', '30');
        $categoriaId = $request->get('categoria_id');
        $ordenar = $request->get('ordenar', 'mas_vendidos');
        $limite = $request->get('limite', 20);
        $fechaDesde = $request->get('fecha_desde', now()->subDays($periodo)->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        
        $categorias = CategoriaProducto::all();
        $productos = $this->getProductosAnalisis($fechaDesde, $fechaHasta, $categoriaId, $ordenar, $limite);
        
        $productosBajoStock = Producto::where('stock', '<', 10)
            ->with(['categoria', 'imagenPrincipal'])
            ->when($categoriaId, fn($q) => $q->where('categoria_id', $categoriaId))
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();
        
        $productosSinVentas = Producto::whereDoesntHave('detallesOrden', function($q) use ($fechaDesde, $fechaHasta) {
                $q->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);
            })
            ->with(['categoria', 'imagenPrincipal'])
            ->when($categoriaId, fn($q) => $q->where('categoria_id', $categoriaId))
            ->where('activo', true)
            ->limit(10)
            ->get();
        
        return view('admin.estadisticas.productos', compact(
            'categorias', 'productos', 'productosBajoStock', 'productosSinVentas', 
            'periodo', 'categoriaId', 'ordenar', 'limite', 'fechaDesde', 'fechaHasta'
        ));
    }
    
    // Análisis de clientes
    public function clientes(Request $request)
    {
        $periodo = $request->get('periodo', '30');
        $limite = $request->get('limite', 20);
        $ordenar = $request->get('ordenar', 'mayor_gasto');
        $tipoCliente = $request->get('tipo_cliente', 'todos'); // NUEVO
        $soloActivos = $request->get('solo_activos', false); // NUEVO
        $fechaDesde = $request->get('fecha_desde', now()->subDays($periodo)->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        
        // Top clientes con ordenamiento configurable
        $query = User::where('is_admin', false)
            ->withSum(['ordenes' => fn($q) => $q->whereIn('estado', ['procesando', 'enviado', 'entregado'])
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])], 'total')
            ->withCount(['ordenes' => fn($q) => $q->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])])
            ->having('ordenes_sum_total', '>', 0);
        
        // NUEVO: Filtro por tipo de cliente (nuevos/antiguos)
        if ($tipoCliente === 'nuevos') {
            $query->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);
        } elseif ($tipoCliente === 'antiguos') {
            $query->where('created_at', '<', $fechaDesde . ' 00:00:00');
        }
        
        // NUEVO: Filtro solo activos (que hayan comprado en el período)
        if ($soloActivos) {
            $query->whereHas('ordenes', fn($q) => $q->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']));
        }
        
        // Aplicar ordenamiento
        switch ($ordenar) {
            case 'menor_gasto':
                $query->orderBy('ordenes_sum_total', 'asc');
                break;
            case 'mas_ordenes':
                $query->orderByDesc('ordenes_count');
                break;
            case 'menos_ordenes':
                $query->orderBy('ordenes_count', 'asc');
                break;
            default: // mayor_gasto
                $query->orderByDesc('ordenes_sum_total');
        }
        
        $topClientes = $query->limit($limite)
            ->get()
            ->map(function($cliente) {
                $cliente->total_gastado = $cliente->ordenes_sum_total ?? 0;
                $cliente->total_ordenes = $cliente->ordenes_count ?? 0;
                return $cliente;
            });
        
        // MEJORADO: Clientes nuevos priorizando activos
        $clientesNuevos = User::where('is_admin', false)
            ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
            ->withCount(['ordenes' => fn($q) => $q->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])])
            ->orderByDesc('ordenes_count') // Primero los que tienen órdenes
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $clientesStats = [
            'total_clientes' => User::where('is_admin', false)->count(),
            'clientes_nuevos' => User::where('is_admin', false)
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])->count(),
            'clientes_activos' => User::where('is_admin', false)
                ->whereHas('ordenes', fn($q) => $q->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']))->count(),
            'ticket_promedio' => Orden::whereIn('estado', ['procesando', 'enviado', 'entregado'])
                ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])->avg('total') ?? 0,
        ];
        
        return view('admin.estadisticas.clientes', compact(
            'topClientes', 'clientesNuevos', 'clientesStats', 
            'periodo', 'limite', 'ordenar', 'tipoCliente', 'soloActivos', 'fechaDesde', 'fechaHasta'
        ));
    }

    public function ventasRealTime()
    {
        return response()->json([
            'ventas_hoy' => Orden::whereDate('created_at', today())
                ->whereIn('estado', ['procesando', 'enviado', 'entregado'])->sum('total'),
            'ordenes_hoy' => Orden::whereDate('created_at', today())->count(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
    
    // MÉTODOS AUXILIARES
    private function getGeneralStats($fechaDesde, $fechaHasta, $categoriaId = null)
    {
        $fechaDesdeCompleta = $fechaDesde . ' 00:00:00';
        $fechaHastaCompleta = $fechaHasta . ' 23:59:59';
        
        $query = Orden::whereIn('estado', ['procesando', 'enviado', 'entregado'])
            ->whereBetween('created_at', [$fechaDesdeCompleta, $fechaHastaCompleta]);
        
        $queryDetalles = DetalleOrden::whereBetween('created_at', [$fechaDesdeCompleta, $fechaHastaCompleta]);
        
        if ($categoriaId) {
            $queryDetalles->whereHas('producto', fn($q) => $q->where('categoria_id', $categoriaId));
        }
        
        return [
            'total_ventas' => $query->sum('total'),
            'total_ordenes' => Orden::whereBetween('created_at', [$fechaDesdeCompleta, $fechaHastaCompleta])->count(),
            'productos_vendidos' => $queryDetalles->sum('cantidad'),
            'ticket_promedio' => $query->avg('total') ?? 0,
        ];
    }
    
    private function getComparison($fechaDesde, $fechaHasta, $fechaDesdePrevio, $fechaHastaPrevio, $categoriaId = null)
    {
        $actual = $this->getGeneralStats($fechaDesde, $fechaHasta, $categoriaId);
        $anterior = $this->getGeneralStats($fechaDesdePrevio, $fechaHastaPrevio, $categoriaId);
        
        $cambio = $anterior['total_ventas'] > 0 
            ? (($actual['total_ventas'] - $anterior['total_ventas']) / $anterior['total_ventas']) * 100 
            : ($actual['total_ventas'] > 0 ? 100 : 0);
        
        return [
            'ventas_actuales' => $actual['total_ventas'],
            'ventas_anteriores' => $anterior['total_ventas'],
            'cambio_porcentaje' => round($cambio, 2),
            'ventas_cambio' => round($cambio, 1),
            'ordenes_cambio' => $this->calcularPorcentajeCambio($anterior['total_ordenes'], $actual['total_ordenes']),
            'productos_cambio' => $this->calcularPorcentajeCambio($anterior['productos_vendidos'], $actual['productos_vendidos']),
            'ticket_cambio' => $this->calcularPorcentajeCambio($anterior['ticket_promedio'], $actual['ticket_promedio']),
        ];
    }
    
    private function calcularPorcentajeCambio($anterior, $actual)
    {
        if ($anterior == 0) return $actual > 0 ? 100 : 0;
        return round((($actual - $anterior) / $anterior) * 100, 1);
    }
    
    private function getTopProductos($fechaDesde, $fechaHasta, $categoriaId, $limite)
    {
        $fechaDesdeCompleta = $fechaDesde . ' 00:00:00';
        $fechaHastaCompleta = $fechaHasta . ' 23:59:59';
        
        return DetalleOrden::select(
                'producto_id',
                DB::raw('SUM(cantidad) as total_vendido'),
                DB::raw('SUM(subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT orden_id) as num_ordenes')
            )
            ->with('producto.imagenPrincipal')
            ->whereBetween('created_at', [$fechaDesdeCompleta, $fechaHastaCompleta])
            ->when($categoriaId, fn($q) => $q->whereHas('producto', fn($q2) => $q2->where('categoria_id', $categoriaId)))
            ->groupBy('producto_id')
            ->orderByDesc('total_ingresos')
            ->limit($limite)
            ->get();
    }
    
    private function getVentasPorDia($fechaDesde, $fechaHasta, $categoriaId = null)
    {
        $fechaDesdeCompleta = $fechaDesde . ' 00:00:00';
        $fechaHastaCompleta = $fechaHasta . ' 23:59:59';
        
        $query = Orden::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as ordenes'),
                DB::raw('SUM(total) as ingresos')
            )
            ->whereIn('estado', ['procesando', 'enviado', 'entregado'])
            ->whereBetween('created_at', [$fechaDesdeCompleta, $fechaHastaCompleta]);
        
        // Si hay filtro de categoría, filtrar por productos de esa categoría
        if ($categoriaId) {
            $query->whereHas('detalles.producto', fn($q) => $q->where('categoria_id', $categoriaId));
        }
        
        return $query->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();
    }
    
    private function getVentasPorHora($fechaDesde, $fechaHasta, $categoriaId = null)
    {
        $fechaDesdeCompleta = $fechaDesde . ' 00:00:00';
        $fechaHastaCompleta = $fechaHasta . ' 23:59:59';
        
        $query = Orden::select(
                DB::raw('HOUR(created_at) as hora'),
                DB::raw('COUNT(*) as ordenes'),
                DB::raw('SUM(total) as ingresos')
            )
            ->whereIn('estado', ['procesando', 'enviado', 'entregado'])
            ->whereBetween('created_at', [$fechaDesdeCompleta, $fechaHastaCompleta]);
        
        // Si hay filtro de categoría, filtrar por productos de esa categoría
        if ($categoriaId) {
            $query->whereHas('detalles.producto', fn($q) => $q->where('categoria_id', $categoriaId));
        }
        
        return $query->groupBy('hora')
            ->orderBy('hora', 'asc')
            ->get();
    }
    
    private function getVentasPorCategoria($fechaDesde, $fechaHasta)
    {
        $fechaDesdeCompleta = $fechaDesde . ' 00:00:00';
        $fechaHastaCompleta = $fechaHasta . ' 23:59:59';
        
        return CategoriaProducto::select('categorias_producto.id', 'categorias_producto.nombre')
            ->join('productos', 'productos.categoria_id', '=', 'categorias_producto.id')
            ->join('detalles_orden', 'detalles_orden.producto_id', '=', 'productos.id')
            ->whereBetween('detalles_orden.created_at', [$fechaDesdeCompleta, $fechaHastaCompleta])
            ->groupBy('categorias_producto.id', 'categorias_producto.nombre')
            ->select(
                'categorias_producto.id',
                'categorias_producto.nombre',
                DB::raw('SUM(detalles_orden.cantidad) as total_vendido'),
                DB::raw('SUM(detalles_orden.subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT detalles_orden.orden_id) as num_ordenes')
            )
            ->get();
    }
    
    private function getDistribucionEstados($fechaDesde, $fechaHasta)
    {
        $fechaDesdeCompleta = $fechaDesde . ' 00:00:00';
        $fechaHastaCompleta = $fechaHasta . ' 23:59:59';
        
        $resultados = Orden::select('estado', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$fechaDesdeCompleta, $fechaHastaCompleta])
            ->groupBy('estado')
            ->get();
        
        return $resultados->map(function($item) {
            return [
                'estado' => $item->estado,
                'total' => $item->total
            ];
        });
    }
    
    private function getProductosAnalisis($fechaDesde, $fechaHasta, $categoriaId, $ordenar, $limite)
    {
        $fechaDesdeCompleta = $fechaDesde . ' 00:00:00';
        $fechaHastaCompleta = $fechaHasta . ' 23:59:59';
        
        $query = DetalleOrden::select(
                'detalles_orden.producto_id',
                DB::raw('SUM(detalles_orden.cantidad) as total_vendido'),
                DB::raw('SUM(detalles_orden.subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT detalles_orden.orden_id) as num_ordenes')
            )
            ->with(['producto.imagenPrincipal', 'producto.categoria'])
            ->whereBetween('detalles_orden.created_at', [$fechaDesdeCompleta, $fechaHastaCompleta]) // ESPECIFICAR TABLA
            ->when($categoriaId, fn($q) => $q->whereHas('producto', fn($q2) => $q2->where('categoria_id', $categoriaId)))
            ->groupBy('detalles_orden.producto_id');
        
        switch ($ordenar) {
            case 'mas_ingresos':
                $query->orderByDesc('total_ingresos');
                break;
            case 'mas_ordenes':
                $query->orderByDesc('num_ordenes');
                break;
            case 'precio_mayor':
                // CORREGIDO: Especificar tabla en el WHERE
                $query->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                    ->orderByDesc('productos.precio')
                    ->select('detalles_orden.producto_id', 
                            DB::raw('SUM(detalles_orden.cantidad) as total_vendido'),
                            DB::raw('SUM(detalles_orden.subtotal) as total_ingresos'),
                            DB::raw('COUNT(DISTINCT detalles_orden.orden_id) as num_ordenes'));
                break;
            case 'precio_menor':
                // CORREGIDO: Especificar tabla en el WHERE
                $query->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                    ->orderBy('productos.precio')
                    ->select('detalles_orden.producto_id', 
                            DB::raw('SUM(detalles_orden.cantidad) as total_vendido'),
                            DB::raw('SUM(detalles_orden.subtotal) as total_ingresos'),
                            DB::raw('COUNT(DISTINCT detalles_orden.orden_id) as num_ordenes'));
                break;
            default:
                $query->orderByDesc('total_vendido');
        }
        
        return $query->limit($limite)->get();
    }
}