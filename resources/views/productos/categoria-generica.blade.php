@extends('layouts.app')
@section('title', $categoria->nombre . ' - Catbox')

@push('styles')
<style>
    .categoria-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 0;
        position: relative;
        overflow: hidden;
    }
    .categoria-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .categoria-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background: white;
    }
    .categoria-card:hover {
        transform: translateY(-10px) scale(1.02);
        border-color: #667eea;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    .badge-categoria {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .floating-icon {
        animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<div class="categoria-hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="floating-icon mb-3">
                    <i class="bi bi-box-seam display-1"></i>
                </div>
                <h1 class="display-3 fw-bold mb-3">{{ $categoria->nombre }}</h1>
                <p class="lead mb-4">{{ $categoria->descripcion ?? 'Descubre nuestra colección de ' . $categoria->nombre }}</p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge badge-categoria px-3 py-2">
                        <i class="bi bi-star-fill"></i> Productos de Calidad
                    </span>
                    <span class="badge badge-categoria px-3 py-2">
                        <i class="bi bi-shield-check"></i> 100% Auténtico
                    </span>
                    <span class="badge badge-categoria px-3 py-2">
                        <i class="bi bi-truck"></i> Envío Gratis
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-stars display-1 floating-icon"></i>
            </div>
        </div>
    </div>
</div>

{{-- Filtros y Ordenamiento --}}
<div class="bg-light py-3 border-bottom">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="bi bi-grid-3x3-gap text-purple"></i> 
                    {{ $productos->total() }} productos disponibles
                </h5>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2 justify-content-end flex-wrap">
                    <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                        <option value="{{ route('productos.categoria', $categoria->slug) }}">Más recientes</option>
                        <option value="{{ route('productos.categoria', [$categoria->slug, 'orden' => 'precio_asc']) }}">Precio: Menor a Mayor</option>
                        <option value="{{ route('productos.categoria', [$categoria->slug, 'orden' => 'precio_desc']) }}">Precio: Mayor a Menor</option>
                        <option value="{{ route('productos.categoria', [$categoria->slug, 'orden' => 'nombre']) }}">Nombre A-Z</option>
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
            <div class="card categoria-card h-100 shadow-sm">
                {{-- Imagen del producto --}}
                <div class="position-relative">
                    <img src="{{ producto_imagen($producto) }}" 
                         class="card-img-top" 
                         alt="{{ $producto->nombre }}"
                         style="height: 250px; object-fit: cover;"
                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                    
                    {{-- Badge nuevo --}}
                    @if($producto->created_at->diffInDays(now()) < 7)
                        <span class="position-absolute top-0 start-0 m-2 badge bg-info">
                            <i class="bi bi-star-fill"></i> Nuevo
                        </span>
                    @endif
                    
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
                
                <div class="card-body">
                    <h6 class="card-title fw-bold">{{ Str::limit($producto->nombre, 50) }}</h6>
                    <p class="card-text text-muted small mb-3">
                        {{ Str::limit($producto->descripcion, 80) }}
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0 text-purple fw-bold">
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
            <a href="{{ route('productos.index') }}" class="btn btn-catbox mt-3">
                Ver todos los productos
            </a>
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

{{-- Sección informativa --}}
<div class="bg-light py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <div class="p-4">
                    <i class="bi bi-star-fill display-4 text-warning mb-3"></i>
                    <h5>Calidad Premium</h5>
                    <p class="text-muted">Productos seleccionados cuidadosamente</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4">
                    <i class="bi bi-shield-check display-4 text-success mb-3"></i>
                    <h5>100% Auténtico</h5>
                    <p class="text-muted">Garantía de autenticidad en todos nuestros productos</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4">
                    <i class="bi bi-truck display-4 text-primary mb-3"></i>
                    <h5>Envío Rápido</h5>
                    <p class="text-muted">Recibe tu pedido en 3-5 días hábiles</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-purple {
        color: #667eea;
    }
</style>
@endpush