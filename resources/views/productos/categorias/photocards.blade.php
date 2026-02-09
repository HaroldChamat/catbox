@extends('layouts.app')

@section('title', 'Photocards - Catbox')

@push('styles')
<style>
    .photocard-hero {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 60px 0;
        position: relative;
        overflow: hidden;
    }
    .photocard-hero::after {
        content: '♪ ♫ ♪';
        position: absolute;
        font-size: 100px;
        opacity: 0.1;
        top: 20%;
        right: 10%;
        animation: musicFloat 4s ease-in-out infinite;
    }
    @keyframes musicFloat {
        0%, 100% { transform: rotate(0deg) translateY(0); }
        50% { transform: rotate(10deg) translateY(-10px); }
    }
    .photocard-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background: white;
        position: relative;
        overflow: hidden;
    }
    .photocard-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
        transform: rotate(45deg);
        transition: all 0.5s;
        opacity: 0;
    }
    .photocard-card:hover::before {
        opacity: 1;
        left: 100%;
    }
    .photocard-card:hover {
        transform: translateY(-10px) rotate(-2deg);
        border-color: #f5576c;
        box-shadow: 0 15px 40px rgba(245, 87, 108, 0.4);
    }
    .badge-photocard {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .sparkle {
        animation: sparkle 1.5s ease-in-out infinite;
    }
    @keyframes sparkle {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }
</style>
@endpush

@section('content')
{{-- Hero Section Photocards --}}
<div class="photocard-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="sparkle mb-3">
                    <i class="bi bi-card-image display-1"></i>
                </div>
                <h1 class="display-3 fw-bold mb-3">Photocard Paradise ✨</h1>
                <p class="lead mb-4">Las mejores photocards oficiales de tus artistas K-pop favoritos. ¡Colecciónalas todas!</p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge badge-photocard px-3 py-2">
                        <i class="bi bi-heart-fill"></i> BTS
                    </span>
                    <span class="badge badge-photocard px-3 py-2">
                        <i class="bi bi-heart-fill"></i> BLACKPINK
                    </span>
                    <span class="badge badge-photocard px-3 py-2">
                        <i class="bi bi-heart-fill"></i> Stray Kids
                    </span>
                    <span class="badge badge-photocard px-3 py-2">
                        <i class="bi bi-heart-fill"></i> +Más
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-stars display-1 sparkle"></i>
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
                    <i class="bi bi-collection text-pink"></i> 
                    {{ $productos->total() }} Photocards oficiales
                </h5>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2 justify-content-end flex-wrap">
                    <span class="badge bg-secondary">Todas</span>
                    <span class="badge bg-secondary">BTS</span>
                    <span class="badge bg-secondary">BLACKPINK</span>
                    <span class="badge bg-secondary">Twice</span>
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
            <div class="card photocard-card h-100 shadow-sm">
                {{-- Imagen --}}
                <div class="position-relative">
                    @if($producto->imagenPrincipal)
                        <img src="{{ asset('storage/' . $producto->imagenPrincipal->ruta) }}" 
                             class="card-img-top" 
                             alt="{{ $producto->nombre }}"
                             style="height: 300px; object-fit: cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center" 
                             style="height: 300px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="bi bi-card-image text-white display-2"></i>
                        </div>
                    @endif
                    
                    {{-- Badge especial --}}
                    @if($producto->stock <= 5 && $producto->stock > 0)
                        <span class="position-absolute top-0 start-0 m-2 badge bg-warning">
                            <i class="bi bi-fire"></i> ¡Últimas unidades!
                        </span>
                    @endif
                    
                    {{-- Badge de stock --}}
                    @if($producto->stock > 0)
                        <span class="position-absolute top-0 end-0 m-2 badge bg-success">
                            En Stock
                        </span>
                    @else
                        <span class="position-absolute top-0 end-0 m-2 badge bg-danger">
                            Agotado
                        </span>
                    @endif
                </div>
                
                <div class="card-body">
                    <h6 class="card-title fw-bold text-center">
                        {{ Str::limit($producto->nombre, 45) }}
                    </h6>
                    <p class="card-text text-muted small text-center mb-3">
                        {{ Str::limit($producto->descripcion, 60) }}
                    </p>
                    
                    <div class="text-center mb-3">
                        <h4 class="mb-0 text-pink fw-bold">
                            ${{ number_format($producto->precio, 0, ',', '.') }}
                        </h4>
                        <small class="text-muted">Photocard Oficial</small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('productos.show', $producto->id) }}" 
                           class="btn btn-photocard">
                            <i class="bi bi-eye"></i> Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="text-muted mt-3">No hay photocards disponibles</p>
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
        <h3 class="text-center mb-4">
            <i class="bi bi-star-fill text-warning"></i> 
            ¿Por qué coleccionar photocards?
        </h3>
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <div class="p-4">
                    <i class="bi bi-patch-check display-4 text-success mb-3"></i>
                    <h5>100% Oficiales</h5>
                    <p class="text-muted">Todas nuestras photocards son oficiales de los álbumes</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4">
                    <i class="bi bi-heart-fill display-4 text-danger mb-3"></i>
                    <h5>Ediciones Especiales</h5>
                    <p class="text-muted">Cards raras y difíciles de conseguir</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4">
                    <i class="bi bi-shield-check display-4 text-primary mb-3"></i>
                    <h5>Estado Perfecto</h5>
                    <p class="text-muted">Cards en excelente condición, protegidas</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-pink {
        color: #f5576c;
    }
    .btn-photocard {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        color: white;
        font-weight: 600;
    }
    .btn-photocard:hover {
        background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
        color: white;
        transform: scale(1.05);
    }
</style>
@endpush