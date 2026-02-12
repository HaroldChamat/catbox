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
            ->get()
            ->map(function($categoria) {
                // Calcular stock total sumando el stock de todos los productos
                $categoria->total_stock = \DB::table('productos')
                    ->where('categoria_id', $categoria->id)
                    ->sum('stock');
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

        $slug = Str::slug($request->nombre);

        CategoriaProducto::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'slug' => $slug,
        ]);

        // Crear vista automáticamente para la nueva categoría
        $this->crearVistaCategoria($slug, $request->nombre);

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría creada correctamente y vista generada automáticamente');
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

        // Eliminar vista de la categoría
        $this->eliminarVistaCategoria($categoria->slug);

        $categoria->delete();
        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada');
    }

    /**
     * Crear vista automáticamente para nueva categoría
     */
    private function crearVistaCategoria($slug, $nombre)
    {
        $rutaVista = resource_path("views/productos/categorias/{$slug}.blade.php");

        // Si ya existe, no sobrescribir
        if (file_exists($rutaVista)) {
            return;
        }

        // Generar colores aleatorios para el gradiente
        $colores = $this->generarGradienteAleatorio();

        // Plantilla de la vista
        $contenido = <<<BLADE
@extends('layouts.app')
@section('title', '{$nombre} - Catbox')

@push('styles')
<style>
    .{$slug}-hero {
        background: linear-gradient(135deg, {$colores['color1']} 0%, {$colores['color2']} 100%);
        color: white;
        padding: 60px 0;
        position: relative;
        overflow: hidden;
    }
    .{$slug}-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .{$slug}-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background: white;
    }
    .{$slug}-card:hover {
        transform: translateY(-10px) scale(1.02);
        border-color: {$colores['color1']};
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    .badge-{$slug} {
        background: linear-gradient(135deg, {$colores['color1']} 0%, {$colores['color2']} 100%);
    }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<div class="{$slug}-hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-3">{$nombre}</h1>
                <p class="lead mb-4">{{ \$categoria->descripcion ?? 'Descubre nuestra colección de {$nombre}' }}</p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge badge-{$slug} px-3 py-2">
                        <i class="bi bi-star-fill"></i> Productos de Calidad
                    </span>
                    <span class="badge badge-{$slug} px-3 py-2">
                        <i class="bi bi-shield-check"></i> 100% Auténtico
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="bg-light py-3 border-bottom">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="bi bi-grid-3x3-gap"></i> 
                    {{ \$productos->total() }} productos disponibles
                </h5>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2 justify-content-end flex-wrap">
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option>Más recientes</option>
                        <option>Precio: Menor a Mayor</option>
                        <option>Precio: Mayor a Menor</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Grid de Productos --}}
<div class="container my-5">
    <div class="row g-4">
        @forelse(\$productos as \$producto)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card {$slug}-card h-100 shadow-sm">
                <div class="position-relative">
                    <img src="{{ producto_imagen(\$producto) }}" 
                         class="card-img-top" 
                         alt="{{ \$producto->nombre }}"
                         style="height: 250px; object-fit: cover;"
                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                    
                    @if(\$producto->stock > 0)
                        <span class="position-absolute top-0 end-0 m-2 badge bg-success">
                            <i class="bi bi-check-circle"></i> Disponible
                        </span>
                    @else
                        <span class="position-absolute top-0 end-0 m-2 badge bg-danger">
                            <i class="bi bi-x-circle"></i> Agotado
                        </span>
                    @endif
                </div>
                
                <div class="card-body">
                    <h6 class="card-title fw-bold">{{ Str::limit(\$producto->nombre, 50) }}</h6>
                    <p class="card-text text-muted small mb-3">
                        {{ Str::limit(\$producto->descripcion, 80) }}
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0 fw-bold" style="color: {$colores['color1']}">
                            \${{ number_format(\$producto->precio, 0, ',', '.') }}
                        </h4>
                        <small class="text-muted">
                            <i class="bi bi-box"></i> Stock: {{ \$producto->stock }}
                        </small>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('productos.show', \$producto->id) }}" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-eye"></i> Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="text-muted mt-3">No hay productos disponibles en esta categoría</p>
        </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    @if(\$productos->hasPages())
    <div class="d-flex justify-content-center mt-5">
        {{ \$productos->links() }}
    </div>
    @endif
</div>
@endsection
BLADE;

        // Crear el directorio si no existe
        $directorio = dirname($rutaVista);
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        // Guardar la vista
        file_put_contents($rutaVista, $contenido);
    }

    /**
     * Eliminar vista de categoría
     */
    private function eliminarVistaCategoria($slug)
    {
        $rutaVista = resource_path("views/productos/categorias/{$slug}.blade.php");
        
        if (file_exists($rutaVista)) {
            unlink($rutaVista);
        }
    }

    /**
     * Generar gradiente aleatorio
     */
    private function generarGradienteAleatorio()
    {
        $gradientes = [
            ['color1' => '#667eea', 'color2' => '#764ba2'], // Morado
            ['color1' => '#f093fb', 'color2' => '#f5576c'], // Rosa
            ['color1' => '#43e97b', 'color2' => '#38f9d7'], // Verde
            ['color1' => '#fa709a', 'color2' => '#fee140'], // Naranja
            ['color1' => '#30cfd0', 'color2' => '#330867'], // Azul
            ['color1' => '#a8edea', 'color2' => '#fed6e3'], // Pastel
            ['color1' => '#ff9a9e', 'color2' => '#fecfef'], // Rosa claro
            ['color1' => '#ffecd2', 'color2' => '#fcb69f'], // Durazno
        ];

        return $gradientes[array_rand($gradientes)];
    }
}