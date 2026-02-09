@extends('layouts.app')

@section('title', 'Nendoroid - Catbox')

@push('styles')
<style>
    .nendoroid-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 0;
        position: relative;
        overflow: hidden;
    }
    .nendoroid-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .nendoroid-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background: white;
    }
    .nendoroid-card:hover {
        transform: translateY(-10px) scale(1.02);
        border-color: #667eea;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    .badge-nendoroid {
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
{{-- Hero Section Nendoroid --}}
<div class="nendoroid-hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="floating-icon mb-3">
                    <i class="bi bi-person-badge display-1"></i>
                </div>
                <h1 class="display-3 fw-bold mb-3">Nendoroid Collection</h1>
                <p class="lead mb-4">Descubre las mejores figuras Nendoroid coleccionables con partes intercambiables y expresiones únicas</p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge badge-nendoroid px-3 py-2">
                        <i class="bi bi-star-fill"></i> Edición Limitada
                    </span>
                    <span class="badge badge-nendoroid px-3 py-2">
                        <i class="bi bi-gift"></i> Incluye Accesorios
                    </span>
                    <span class="badge badge-nendoroid px-3 py-2">
                        <i class="bi bi-shield-check"></i> 100% Auténtico
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-box-seam display-1 floating-icon"></i>
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
                    {{ $productos->total() }} Nendoroids disponibles
                </h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active">
                        <i class="bi bi-grid-3x3"></i> Cuadrícula
                    </button>
                    <button type="button" class="btn btn-outline-secondary">
                        <i class="bi bi-list"></i> Lista
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Productos --}}
<div class="container my-5">
    <div class="row g-4">
        @forelse($productos as $producto)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card nendoroid-card h-100 shadow-sm">
                {{-- Imagen del producto --}}
                <div class="position-relative">
                    @if($producto->imagenPrincipal)
                        <img src="{{ asset('storage/' . $producto->imagenPrincipal->ruta) }}" 
                             class="card-img-top" 
                             alt="{{ $producto->nombre }}"
                             style="height: 250px; object-fit: cover;">
                    @else
                        <div class="bg-gradient d-flex align-items-center justify-content-center" 
                             style="height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-image text-white display-3"></i>
                        </div>
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
            <p class="text-muted mt-3">No hay Nendoroids disponibles en este momento</p>
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
                    <h5>Ediciones Exclusivas</h5>
                    <p class="text-muted">Figuras limitadas y difíciles de conseguir</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4">
                    <i class="bi bi-puzzle display-4 text-primary mb-3"></i>
                    <h5>Partes Intercambiables</h5>
                    <p class="text-muted">Múltiples expresiones y accesorios incluidos</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4">
                    <i class="bi bi-shield-check display-4 text-success mb-3"></i>
                    <h5>Garantía de Autenticidad</h5>
                    <p class="text-muted">Productos 100% originales y certificados</p>
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
    .bg-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>
@endpush