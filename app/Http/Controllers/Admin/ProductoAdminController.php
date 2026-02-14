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
    /**
     * Mostrar lista de productos
     */
    public function index(Request $request)
    {
        $categorias = CategoriaProducto::orderBy('nombre')->get();
        
        $query = Producto::with(['categoria', 'imagenPrincipal']);

        // Filtros
        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        if ($request->filled('stock_bajo')) {
            $query->where('stock', '<', 10);
        }

        $productos = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear()
    {
        $categorias = CategoriaProducto::all();
        return view('admin.productos.form', compact('categorias'));
    }

    /**
     * Guardar nuevo producto
     */
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
            // Crear producto
            $producto = new Producto();
            $producto->nombre = $request->nombre;
            $producto->descripcion = $request->descripcion;
            $producto->precio = $request->precio;
            $producto->stock = $request->stock;
            $producto->categoria_id = $request->categoria_id;
            $producto->activo = $request->has('activo') ? 1 : 0;
            $producto->slug = Str::slug($request->nombre) . '-' . time();
            $producto->save();

            // Guardar imágenes
            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $index => $imagen) {
                    $ruta = $imagen->store('productos', 'public');

                    $img = new ImagenProducto();
                    $img->producto_id = $producto->id;
                    $img->ruta = $ruta;
                    $img->es_principal = ($index === 0);
                    $img->save();
                }
            }

            return redirect()->route('admin.productos.index')
                ->with('success', "Producto \"{$producto->nombre}\" creado correctamente.");
                
        } catch (\Exception $e) {
            Log::error('Error al crear producto: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id)
    {
        $producto = Producto::with(['imagenes', 'categoria'])->findOrFail($id);
        $categorias = CategoriaProducto::all();
        return view('admin.productos.form', compact('producto', 'categorias'));
    }

    /**
     * Actualizar producto - CON LOGGING EXTREMO
     */
    public function actualizar(Request $request, $id)
    {
        Log::info('═══════════════════════════════════════════════════════');
        Log::info('🟢 INICIO DE actualizar() - Producto ID: ' . $id);
        Log::info('Método HTTP recibido: ' . $request->method());
        Log::info('Ruta actual: ' . $request->path());
        Log::info('Todos los datos del request:', $request->all());
        Log::info('═══════════════════════════════════════════════════════');
        
        // Validar datos
        $validated = $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias_producto,id',
            'imagenes'     => 'nullable|array',
            'imagenes.*'   => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        Log::info('✅ Validación exitosa', ['datos' => $validated]);

        try {
            // Verificar que el producto existe ANTES
            Log::info('🔍 Buscando producto ANTES de actualizar...');
            $productoAntes = DB::table('productos')->where('id', $id)->first();
            
            if (!$productoAntes) {
                Log::error('❌ Producto NO EXISTE antes de actualizar');
                throw new \Exception('Producto no encontrado');
            }
            
            Log::info('✅ Producto existe ANTES:', [
                'id' => $productoAntes->id,
                'nombre' => $productoAntes->nombre,
                'categoria_id' => $productoAntes->categoria_id
            ]);

            // Actualizar usando query builder RAW
            Log::info('🔄 Ejecutando UPDATE con Query Builder...');
            
            $affected = DB::table('productos')
                ->where('id', $id)
                ->update([
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'],
                    'precio' => $validated['precio'],
                    'stock' => $validated['stock'],
                    'categoria_id' => $validated['categoria_id'],
                    'activo' => $request->has('activo') ? 1 : 0,
                    'updated_at' => now(),
                ]);

            Log::info('✅ UPDATE ejecutado', ['filas_afectadas' => $affected]);

            // Verificar que el producto EXISTE después del update
            Log::info('🔍 Verificando producto DESPUÉS de actualizar...');
            $productoDespues = DB::table('productos')->where('id', $id)->first();
            
            if (!$productoDespues) {
                Log::error('🚨 ¡PRODUCTO DESAPARECIÓ DESPUÉS DEL UPDATE!');
                Log::error('Esto significa que algo LO BORRÓ durante o después del UPDATE');
                throw new \Exception('El producto fue eliminado inesperadamente');
            }
            
            Log::info('✅ Producto SIGUE EXISTIENDO después del UPDATE:', [
                'id' => $productoDespues->id,
                'nombre' => $productoDespues->nombre,
                'categoria_id' => $productoDespues->categoria_id
            ]);

            // Agregar nuevas imágenes si existen
            if ($request->hasFile('imagenes')) {
                Log::info('📸 Procesando imágenes nuevas...');
                
                $tienePrincipal = ImagenProducto::where('producto_id', $id)
                    ->where('es_principal', true)
                    ->exists();

                foreach ($request->file('imagenes') as $index => $imagen) {
                    $ruta = $imagen->store('productos', 'public');

                    $img = new ImagenProducto();
                    $img->producto_id = $id;
                    $img->ruta = $ruta;
                    $img->es_principal = (!$tienePrincipal && $index === 0);
                    $img->save();

                    if ($index === 0 && !$tienePrincipal) {
                        $tienePrincipal = true;
                    }
                }
                
                Log::info('✅ Imágenes guardadas correctamente');
            }

            // Verificación FINAL antes de redirigir
            Log::info('🔍 Verificación FINAL antes de redirigir...');
            $productoFinal = DB::table('productos')->where('id', $id)->first();
            
            if (!$productoFinal) {
                Log::error('🚨 ¡PRODUCTO DESAPARECIÓ ANTES DE REDIRIGIR!');
                Log::error('Se borró DESPUÉS de guardar las imágenes');
                throw new \Exception('El producto fue eliminado inesperadamente');
            }
            
            Log::info('✅ Producto existe en verificación final');
            Log::info('🟢 FIN DE actualizar() - TODO CORRECTO');
            Log::info('═══════════════════════════════════════════════════════');

            return redirect()->route('admin.productos.index')
                ->with('success', "Producto \"{$productoDespues->nombre}\" actualizado correctamente.");
                
        } catch (\Exception $e) {
            Log::error('❌ ERROR EN actualizar():', [
                'producto_id' => $id,
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar producto
     */
    public function eliminar($id)
    {
        Log::info('🗑️ MÉTODO eliminar() llamado para producto ID: ' . $id);
        
        try {
            $producto = Producto::with('imagenes')->findOrFail($id);

            // Eliminar imágenes del storage
            foreach ($producto->imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->ruta);
            }

            $nombre = $producto->nombre;
            
            // Eliminar usando query builder RAW
            DB::table('imagenes_producto')->where('producto_id', $id)->delete();
            DB::table('productos')->where('id', $id)->delete();
            
            Log::info('✅ Producto eliminado correctamente desde método eliminar()');

            return redirect()->route('admin.productos.index')
                ->with('success', "Producto \"{$nombre}\" eliminado correctamente.");
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar producto: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar imagen de producto
     */
    public function eliminarImagen($id, $imgId)
    {
        try {
            $imagen = ImagenProducto::where('producto_id', $id)->findOrFail($imgId);

            // Eliminar archivo
            Storage::disk('public')->delete($imagen->ruta);

            $eraPrincipal = $imagen->es_principal;
            
            // Eliminar usando query builder
            DB::table('imagenes_producto')->where('id', $imgId)->delete();

            // Si era la principal, asignar otra
            if ($eraPrincipal) {
                $otra = ImagenProducto::where('producto_id', $id)->first();
                if ($otra) {
                    DB::table('imagenes_producto')
                        ->where('id', $otra->id)
                        ->update(['es_principal' => true]);
                }
            }

            return back()->with('success', 'Imagen eliminada correctamente.');
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar imagen: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la imagen.');
        }
    }
}