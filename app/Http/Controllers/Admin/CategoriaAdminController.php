<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriaAdminController extends Controller
{
    public function index()
    {
        $categorias = CategoriaProducto::withCount('productos')
            ->with(['productos' => function($query) {
                $query->select('categoria_id')
                    ->selectRaw('SUM(stock) as total_stock')
                    ->groupBy('categoria_id');
            }])
            ->get()
            ->map(function($categoria) {
                $categoria->total_stock = $categoria->productos->sum('stock');
                return $categoria;
            });

        return view('admin.categorias.index', compact('categorias'));
    }

    public function crear()
    {
        return view('admin.categorias.form');
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias_producto,nombre',
            'descripcion' => 'nullable|string',
        ]);

        CategoriaProducto::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'slug' => Str::slug($request->nombre),
        ]);

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría creada correctamente');
    }

    public function editar($id)
    {
        $categoria = CategoriaProducto::findOrFail($id);
        return view('admin.categorias.form', compact('categoria'));
    }

    public function actualizar(Request $request, $id)
    {
        $categoria = CategoriaProducto::findOrFail($id);
        
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias_producto,nombre,' . $id,
            'descripcion' => 'nullable|string',
        ]);

        $categoria->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'slug' => Str::slug($request->nombre),
        ]);

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría actualizada correctamente');
    }

    public function eliminar($id)
    {
        $categoria = CategoriaProducto::findOrFail($id);
        
        if ($categoria->productos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una categoría con productos asociados');
        }

        $categoria->delete();
        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada');
    }
}