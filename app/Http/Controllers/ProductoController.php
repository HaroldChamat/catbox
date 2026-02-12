<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\CategoriaProducto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function landing()
    {
        $categorias = CategoriaProducto::all();
        $productosDestacados = Producto::where('activo', true)
            ->with(['categoria', 'imagenPrincipal'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
        return view('landing', compact('categorias', 'productosDestacados'));
    }

    public function index(Request $request)
    {
        $categorias = CategoriaProducto::all();
        
        // Productos más vendidos (top 9 para el carrusel)
        $productosMasVendidos = Producto::where('activo', true)
            ->with(['categoria', 'imagenPrincipal'])
            ->withCount(['detallesOrden as total_vendido' => function($query) {
                $query->select(\DB::raw('COALESCE(SUM(cantidad), 0)'));
            }])
            ->orderByDesc('total_vendido')
            ->limit(9)
            ->get();

        // Query de productos con filtros
        $query = Producto::where('activo', true)
            ->with(['categoria', 'imagenPrincipal']);

        // Filtro por búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        // Filtro por rango de precio
        if ($request->filled('precio')) {
            $rango = explode('-', $request->precio);
            if (count($rango) === 2) {
                $query->whereBetween('precio', [(int)$rango[0], (int)$rango[1]]);
            }
        }

        // Filtro por disponibilidad
        if ($request->boolean('disponible')) {
            $query->where('stock', '>', 0);
        }

        // Ordenamiento
        switch ($request->get('orden', 'recientes')) {
            case 'precio_asc':
                $query->orderBy('precio', 'asc');
                break;
            case 'precio_desc':
                $query->orderBy('precio', 'desc');
                break;
            case 'nombre':
                $query->orderBy('nombre', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $productos = $query->paginate(12);

        return view('productos.index', compact('categorias', 'productos', 'productosMasVendidos'));
    }

    public function categoria($slug)
    {
        $categoria = CategoriaProducto::where('slug', $slug)->firstOrFail();
        $productos = Producto::where('categoria_id', $categoria->id)
            ->where('activo', true)
            ->with(['imagenPrincipal'])
            ->paginate(12);
        
        // Verificar si existe vista personalizada para esta categoría
        $vistaPersonalizada = "productos.categorias.{$slug}";
        $rutaVista = resource_path("views/productos/categorias/{$slug}.blade.php");
        
        // Si existe la vista personalizada, usarla; si no, usar la genérica
        if (file_exists($rutaVista)) {
            return view($vistaPersonalizada, compact('categoria', 'productos'));
        }
        
        // Vista genérica para categorías sin vista personalizada
        return view('productos.categoria-generica', compact('categoria', 'productos'));
    }

    public function show($id)
    {
        $producto = Producto::with(['categoria', 'imagenes'])
            ->where('activo', true)
            ->findOrFail($id);
        $relacionados = Producto::where('categoria_id', $producto->categoria_id)
            ->where('id', '!=', $producto->id)
            ->where('activo', true)
            ->with(['imagenPrincipal'])
            ->limit(4)
            ->get();
        return view('productos.show', compact('producto', 'relacionados'));
    }

    public function buscar(Request $request)
    {
        $query = $request->input('q');
        $productos = Producto::where('activo', true)
            ->where(function ($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('descripcion', 'like', "%{$query}%");
            })
            ->with(['categoria', 'imagenPrincipal'])
            ->paginate(12);
        return view('productos.buscar', compact('productos', 'query'));
    }
}