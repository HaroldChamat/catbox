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

    public function index()
    {
        $categorias = CategoriaProducto::all();
        $productos = Producto::where('activo', true)
            ->with(['categoria', 'imagenPrincipal'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);
        return view('productos.index', compact('categorias', 'productos'));
    }

    public function categoria($slug)
    {
        $categoria = CategoriaProducto::where('slug', $slug)->firstOrFail();
        $productos = Producto::where('categoria_id', $categoria->id)
            ->where('activo', true)
            ->with(['imagenPrincipal'])
            ->paginate(12);
        $vista = match($slug) {
            'nendoroid'  => 'productos.categorias.nendoroid',
            'photocards' => 'productos.categorias.photocards',
            'llaveros'   => 'productos.categorias.llaveros',
            default      => 'productos.categoria',
        };
        return view($vista, compact('categoria', 'productos'));
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