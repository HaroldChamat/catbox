<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaProducto;
use App\Models\ImagenProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

        try {
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
        } catch (\Exception $e) {
            Log::error('Error al crear producto: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear el producto. Por favor, intenta de nuevo.');
        }
    }

    public function editar($id)
    {
        $producto   = Producto::with(['imagenes', 'categoria'])->findOrFail($id);
        $categorias = CategoriaProducto::all();
        return view('admin.productos.form', compact('producto', 'categorias'));
    }

    public function actualizar(Request $request, $id)
    {
        // CRÍTICO: Usar transacción de base de datos para prevenir borrados accidentales
        DB::beginTransaction();
        
        try {
            // Verificar que el producto existe ANTES de hacer cualquier cosa
            $producto = Producto::findOrFail($id);
            
            // Log para debugging
            Log::info('Actualizando producto ID: ' . $id, [
                'nombre_actual' => $producto->nombre,
                'stock_actual' => $producto->stock,
                'datos_recibidos' => $request->except(['imagenes', '_token', '_method'])
            ]);

            // Validar los datos recibidos
            $validated = $request->validate([
                'nombre'       => 'required|string|max:255',
                'descripcion'  => 'nullable|string',
                'precio'       => 'required|numeric|min:0',
                'stock'        => 'required|integer|min:0',
                'categoria_id' => 'required|exists:categorias_producto,id',
                'imagenes'     => 'nullable|array',
                'imagenes.*'   => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            // IMPORTANTE: NO usar update() con array, usar asignación individual
            $producto->nombre = $validated['nombre'];
            $producto->descripcion = $validated['descripcion'] ?? null;
            $producto->precio = $validated['precio'];
            $producto->stock = $validated['stock'];
            $producto->categoria_id = $validated['categoria_id'];
            
            // Manejar el checkbox activo
            $producto->activo = $request->has('activo') ? 1 : 0;
            
            // Log antes de guardar
            Log::info('Valores a guardar:', [
                'nombre' => $producto->nombre,
                'stock' => $producto->stock,
                'precio' => $producto->precio,
                'activo' => $producto->activo
            ]);
            
            // Guardar cambios - ESTO NO DEBE BORRAR EL PRODUCTO
            $guardado = $producto->save();
            
            if (!$guardado) {
                throw new \Exception('No se pudo guardar el producto');
            }
            
            // Verificar que el producto sigue existiendo después de guardar
            $productoVerificacion = Producto::find($id);
            if (!$productoVerificacion) {
                throw new \Exception('El producto desapareció después de guardar');
            }

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

                        if ($index === 0 && !$tienePrincipal) {
                            $tienePrincipal = true;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al guardar imagen: ' . $e->getMessage());
                    }
                }
            }

            // Si todo salió bien, confirmar la transacción
            DB::commit();
            
            Log::info('Producto actualizado exitosamente ID: ' . $id);

            return redirect()->route('admin.productos.index')
                ->with('success', "Producto \"{$producto->nombre}\" actualizado correctamente.");
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación al actualizar producto: ' . $e->getMessage());
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Si hay cualquier error, REVERTIR todos los cambios
            DB::rollBack();
            
            Log::error('Error CRÍTICO al actualizar producto ID ' . $id . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['imagenes', '_token', '_method'])
            ]);
            
            return back()->withInput()->with('error', 'Error al actualizar el producto: ' . $e->getMessage() . '. No se realizaron cambios.');
        }
    }

    public function eliminar($id)
    {
        try {
            $producto = Producto::with('imagenes')->findOrFail($id);

            // Eliminar imágenes del storage
            foreach ($producto->imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->ruta);
            }

            $nombre = $producto->nombre;
            $producto->delete();

            return redirect()->route('admin.productos.index')
                ->with('success', "Producto \"{$nombre}\" eliminado correctamente.");
        } catch (\Exception $e) {
            Log::error('Error al eliminar producto: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el producto. Por favor, intenta de nuevo.');
        }
    }

    public function eliminarImagen($id, $imgId)
    {
        try {
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

            return back()->with('success', 'Imagen eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar imagen: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la imagen. Por favor, intenta de nuevo.');
        }
    }
}