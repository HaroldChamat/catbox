@extends('layouts.app')
@section('title', 'Resultados de búsqueda: ' . $query . ' - Catbox')

@section('content')
<div class="container my-5">
    
    {{-- Encabezado de búsqueda --}}
    <div class="mb-4">
        <h2 class="fw-700">
            <i class="bi bi-search text-danger me-2"></i>
            Resultados para: <span class="text-danger">"{{ $query }}"</span>
        </h2>
        <p class="text-muted">{{ $productos->total() }} productos encontrados</p>
    </div>

    {{-- Resultados --}}
    @if($productos->count() > 0)
    <div class="row g-4">
        @foreach($productos as $producto)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card h-100 shadow-sm" style="border-radius: 16px; transition: transform 0.3s; border: none;">
                <div class="position-relative">
                    <img src="{{ producto_imagen($producto) }}" 
                         class="card-img-top" 
                         style="height: 250px; object-fit: cover;"
                         alt="{{ $producto->nombre }}"
                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                    
                    {{-- Badge de categoría --}}
                    <span class="position-absolute top-0 start-0 m-2 badge bg-secondary">
                        {{ $producto->categoria->nombre }}
                    </span>
                    
                    {{-- Badge de stock --}}
                    @if($producto->stock > 0)
                    <span class="position-absolute top-0 end-0 m-2 badge bg-success">
                        <i class="bi bi-check-circle"></i> Disponible
                    </span>
                    @else
                    <span class="position-absolute top-0 end-0 m-2 badge bg-danger">
                        <i class="bi bi-x-circle"></i> Agotado
                    </span>
                    @endif
                </div>

                <div class="card-body d-flex flex-column">
                    <h6 class="fw-700 mb-2">{{ Str::limit($producto->nombre, 50) }}</h6>
                    <p class="text-muted small flex-grow-1">{{ Str::limit($producto->descripcion, 70) }}</p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <h5 class="text-danger fw-800 mb-0">${{ number_format($producto->precio, 0, ',', '.') }}</h5>
                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-sm btn-catbox">
                            Ver más
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Paginación --}}
    @if($productos->hasPages())
    <div class="d-flex justify-content-center mt-5">
        {{ $productos->appends(['q' => $query])->links() }}
    </div>
    @endif

    @else
    {{-- Sin resultados --}}
    <div class="text-center py-5">
        <i class="bi bi-search display-1 text-muted"></i>
        <h4 class="mt-3">No encontramos productos con "{{ $query }}"</h4>
        <p class="text-muted">Intenta con otras palabras clave o explora nuestras categorías</p>
        
        <div class="mt-4">
            <h5 class="mb-3">Explora por categoría:</h5>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                @foreach($categorias as $cat)
                <a href="{{ route('productos.categoria', $cat->slug) }}" class="btn btn-outline-secondary">
                    {{ $cat->nombre }}
                </a>
                @endforeach
            </div>
        </div>

        <a href="{{ route('productos.index') }}" class="btn btn-catbox mt-4">
            <i class="bi bi-grid"></i> Ver todos los productos
        </a>
    </div>
    @endif
</div>

@push('styles')
<style>
    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
    }
</style>
@endpush
@endsection