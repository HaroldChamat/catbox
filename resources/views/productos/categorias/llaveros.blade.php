@extends('layouts.app')

@section('title', 'Llaveros - Catbox')

@push('styles')
<style>
    .llavero-hero {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        padding: 60px 0;
        position: relative;
        overflow: hidden;
    }
    .llavero-hero::before {
        content: '🔑';
        position: absolute;
        font-size: 200px;
        opacity: 0.1;
        top: -20%;
        right: -5%;
        animation: swing 3s ease-in-out infinite;
    }
    @keyframes swing {
        0%, 100% { transform: rotate(-5deg); }
        50% { transform: rotate(5deg); }
    }
    .llavero-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background: white;
    }
    .llavero-card:hover {
        transform: translateY(-8px);
        border-color: #38f9d7;
        box-shadow: 0 10px 25px rgba(56, 249, 215, 0.3);
    }
    .badge-llavero {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    .key-icon {
        animation: keyRotate 2s ease-in-out infinite;
    }
    @keyframes keyRotate {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-15deg); }
        75% { transform: rotate(15deg); }
    }
</style>
@endpush

@section('content')
{{-- Hero Section Llaveros --}}
<div class="llavero-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="key-icon mb-3">
                    <i class="bi bi-key display-1"></i>
                </div>
                <h1 class="display-3 fw-bold mb-3">Llaveros Únicos 🔑</h1>
                <p class="lead mb-4">Accesorios perfectos para personalizar tus llaves, mochilas y más. Diseños de anime, manga y videojuegos.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge badge-llavero px-3 py-2">
                        <i class="bi bi-lightning-fill"></i> Acrílico Premium
                    </span>
                    <span class="badge badge-llavero px-3 py-2">
                        <i class="bi bi-gem"></i> Diseños Exclusivos
                    </span>
                    <span class="badge badge-llavero px-3 py-2">
                        <i class="bi bi-award"></i> Alta Calidad
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-bag-heart display-1 key-icon"></i>
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
                    <i class="bi bi-collection text-teal"></i> 
                    {{ $productos->total() }} Llaveros disponibles
                </h5>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2 justify-content-end flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option>Más recientes</option>
                        <option>Precio: Menor a Mayor</option>
                        <option>Precio: Mayor a Menor</option>
                        <option>Más populares</option>
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
            <div class="card llavero-card h-100 shadow-sm">
                {{-- Imagen --}}
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
                    <div class="position-absolute bottom-0 start-0 m-2">
                        @if($producto->stock > 10)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Stock: {{ $producto->stock }}
                            </span>
                        @elseif($producto->stock > 0)
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-triangle"></i> Quedan {{ $producto->stock }}
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Agotado
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <h6 class="card-title fw-bold">
                        {{ Str::limit($producto->nombre, 45) }}
                    </h6>
                    <p class="card-text text-muted small mb-2">
                        <i class="bi bi-tag"></i> {{ Str::limit($producto->descripcion, 70) }}
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0 text-teal fw-bold">
                                ${{ number_format($producto->precio, 0, ',', '.') }}
                            </h4>
                            <small class="text-success">
                                <i class="bi bi-truck"></i> Envío gratis
                            </small>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('productos.show', $producto->id) }}" 
                           class="btn btn-llavero">
                            <i class="bi bi-cart-plus"></i> Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="text-muted mt-3">No hay llaveros disponibles</p>
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

{{-- Categorías de Llaveros --}}
<div class="bg-light py-5">
    <div class="container">
        <h3 class="text-center mb-4">
            <i class="bi bi-grid-3x3-gap"></i> Categorías Populares
        </h3>
        <div class="row text-center g-3">
            <div class="col-md-3 col-6">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body">
                        <i class="bi bi-controller display-4 text-primary mb-2"></i>
                        <h6>Anime & Manga</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body">
                        <i class="bi bi-hearts display-4 text-danger mb-2"></i>
                        <h6>Kawaii</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body">
                        <i class="bi bi-joystick display-4 text-success mb-2"></i>
                        <h6>Videojuegos</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body">
                        <i class="bi bi-stars display-4 text-warning mb-2"></i>
                        <h6>Edición Especial</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Beneficios --}}
<div class="container py-5">
    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <div class="p-4">
                <i class="bi bi-gem display-4 text-info mb-3"></i>
                <h5>Material Premium</h5>
                <p class="text-muted">Acrílico de alta calidad con impresión duradera</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="p-4">
                <i class="bi bi-palette display-4 text-danger mb-3"></i>
                <h5>Diseños Únicos</h5>
                <p class="text-muted">Ilustraciones exclusivas de tus series favoritas</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="p-4">
                <i class="bi bi-shield-check display-4 text-success mb-3"></i>
                <h5>Garantía Total</h5>
                <p class="text-muted">Si no te gusta, te devolvemos tu dinero</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-teal {
        color: #38f9d7;
    }
    .btn-llavero {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        border: none;
        color: white;
        font-weight: 600;
    }
    .btn-llavero:hover {
        background: linear-gradient(135deg, #38f9d7 0%, #43e97b 100%);
        color: white;
        transform: scale(1.05);
    }
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
</style>
@endpush