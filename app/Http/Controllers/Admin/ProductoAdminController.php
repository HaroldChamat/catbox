<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaProducto;
use App\Models\ImagenProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductoAdminController extends Controller
{
    public function index(Request $request)
    {
        // Categorías para los filtros
        $categorias = CategoriaProducto::orderBy('nombre')->get();
        
        $query = Producto::with(['categoria', 'imagenPrincipal']);

        // Filtro por nombre
        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtro por estado
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        // Filtro por stock bajo
        if ($request->filled('stock_bajo')) {
            $query->where('stock', '<', 10);
        }

        $productos = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    public function crear()
    {
        $categorias = CategoriaProducto::all();
        return view('admin.productos.form', compact('categorias'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias_producto,id',
            'activo'       => 'boolean',
            'imagenes'     => 'nullable|array',
            'imagenes.*'   => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $producto = Producto::create([
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'precio'       => $request->precio,
            'stock'        => $request->stock,
            'categoria_id' => $request->categoria_id,
            'activo'       => $request->boolean('activo', true),
            'slug'         => Str::slug($request->nombre) . '-' . time(),
        ]);

        // Guardar imágenes
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $index => $imagen) {
                $ruta = $imagen->store('productos', 'public');

                ImagenProducto::create([
                    'producto_id'  => $producto->id,
                    'ruta'         => $ruta,
                    'es_principal' => $index === 0, // primera imagen = principal
                ]);
            }
        }

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto \"{$producto->nombre}\" creado correctamente.");
    }

    public function editar($id)
    {
        $producto   = Producto::with(['imagenes', 'categoria'])->findOrFail($id);
        $categorias = CategoriaProducto::all();
        return view('admin.productos.form', compact('producto', 'categorias'));
    }

    public function actualizar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias_producto,id',
            'imagenes'     => 'nullable|array',
            'imagenes.*'   => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Actualizar solo los campos del formulario
        $producto->update([
            'nombre'       => $validated['nombre'],
            'descripcion'  => $validated['descripcion'],
            'precio'       => $validated['precio'],
            'stock'        => $validated['stock'],
            'categoria_id' => $validated['categoria_id'],
            'activo'       => $request->has('activo') ? true : false,
        ]);

        // Agregar nuevas imágenes si existen
        if ($request->hasFile('imagenes')) {
            $tienePrincipal = $producto->imagenes()->where('es_principal', true)->exists();

            foreach ($request->file('imagenes') as $index => $imagen) {
                try {
                    $ruta = $imagen->store('productos', 'public');

                    ImagenProducto::create([
                        'producto_id'  => $producto->id,
                        'ruta'         => $ruta,
                        'es_principal' => !$tienePrincipal && $index === 0,
                    ]);

                    // Después de agregar la primera imagen, ya hay principal
                    if ($index === 0 && !$tienePrincipal) {
                        $tienePrincipal = true;
                    }
                } catch (\Exception $e) {
                    // Log del error pero continuar con las demás imágenes
                    \Log::error('Error al guardar imagen: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto \"{$producto->nombre}\" actualizado correctamente.");
    }

    public function eliminar($id)
    {
        $producto = Producto::with('imagenes')->findOrFail($id);

        // Eliminar imágenes del storage
        foreach ($producto->imagenes as $imagen) {
            Storage::disk('public')->delete($imagen->ruta);
        }

        $nombre = $producto->nombre;
        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto \"{$nombre}\" eliminado.");
    }

    public function eliminarImagen($id, $imgId)
    {
        $imagen = ImagenProducto::where('producto_id', $id)->findOrFail($imgId);

        Storage::disk('public')->delete($imagen->ruta);

        $eraPrincipal = $imagen->es_principal;
        $imagen->delete();

        // Si era la principal, asignar otra
        if ($eraPrincipal) {
            $otra = ImagenProducto::where('producto_id', $id)->first();
            if ($otra) {
                $otra->update(['es_principal' => true]);
            }
        }

        return back()->with('success', 'Imagen eliminada.');
    }
}