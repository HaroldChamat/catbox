@extends('layouts.app')
@section('title', 'Zeialab - Catbox')

@push('styles')
<style>
    .zeialab-hero {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: white;
        padding: 60px 0;
        position: relative;
        overflow: hidden;
    }
    .zeialab-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .zeialab-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background: white;
    }
    .zeialab-card:hover {
        transform: translateY(-10px) scale(1.02);
        border-color: #a8edea;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    .badge-zeialab {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<div class="zeialab-hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-3">Zeialab</h1>
                <p class="lead mb-4">{{ $categoria->descripcion ?? 'Descubre nuestra colección de Zeialab' }}</p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge badge-zeialab px-3 py-2">
                        <i class="bi bi-star-fill"></i> Productos de Calidad
                    </span>
                    <span class="badge badge-zeialab px-3 py-2">
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
                    {{ $productos->total() }} productos disponibles
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
        @forelse($productos as $producto)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card zeialab-card h-100 shadow-sm">
                <div class="position-relative">
                    <img src="{{ producto_imagen($producto) }}" 
                         class="card-img-top" 
                         alt="{{ $producto->nombre }}"
                         style="height: 250px; object-fit: cover;"
                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                    
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
                
                <div class="card-body">
                    <h6 class="card-title fw-bold">{{ Str::limit($producto->nombre, 50) }}</h6>
                    <p class="card-text text-muted small mb-3">
                        {{ Str::limit($producto->descripcion, 80) }}
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0 fw-bold" style="color: #a8edea">
                            ${{ number_format($producto->precio, 0, ',', '.') }}
                        </h4>
                        <small class="text-muted">
                            <i class="bi bi-box"></i> Stock: {{ $producto->stock }}
                        </small>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('productos.show', $producto->id) }}" 
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
    @if($productos->hasPages())
    <div class="d-flex justify-content-center mt-5">
        {{ $productos->links() }}
    </div>
    @endif
</div>
@endsection