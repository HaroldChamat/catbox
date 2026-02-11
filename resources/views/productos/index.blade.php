@extends('layouts.app')

@section('title', 'Catbox - Tu tienda de coleccionables')

@section('content')
{{-- Hero Section --}}
<div class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Bienvenido a Catbox</h1>
                <p class="lead text-muted">Descubre los mejores Nendoroids, Photocards y Llaveros coleccionables</p>
                <a href="{{ route('productos.index') }}" class="btn btn-catbox btn-lg">
                    <i class="bi bi-shop"></i> Ver todos los productos
                </a>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-box-seam display-1 text-danger"></i>
            </div>
        </div>
    </div>
</div>

{{-- Categorías --}}
<div class="container my-5">
    <h2 class="text-center mb-4">Nuestras Categorías</h2>
    <div class="row g-4">
        @foreach($categorias as $categoria)
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-danger mb-3">
                        @if($categoria->slug === 'nendoroid')
                            <i class="bi bi-person-badge"></i>
                        @elseif($categoria->slug === 'photocards')
                            <i class="bi bi-card-image"></i>
                        @else
                            <i class="bi bi-key"></i>
                        @endif
                    </div>
                    <h4>{{ $categoria->nombre }}</h4>
                    <p class="text-muted">{{ $categoria->descripcion }}</p>
                    <a href="{{ route('productos.categoria', $categoria->slug) }}" class="btn btn-outline-danger">
                        Ver productos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Productos Destacados --}}
<div class="container my-5">
    <h2 class="text-center mb-4">Productos Destacados</h2>
    <div class="row g-4">
        @forelse($productosDestacados as $producto)
        <div class="col-md-3 col-sm-6">
            <div class="card card-product shadow-sm">
                <img src="{{ producto_imagen($producto) }}" 
                     class="card-img-top" 
                     alt="{{ $producto->nombre }}"
                     style="height: 200px; object-fit: cover;"
                     onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                
                <div class="card-body">
                    <span class="badge bg-secondary mb-2">{{ $producto->categoria->nombre }}</span>
                    <h5 class="card-title">{{ Str::limit($producto->nombre, 30) }}</h5>
                    <p class="card-text text-muted small">{{ Str::limit($producto->descripcion, 60) }}</p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="text-danger mb-0">${{ number_format($producto->precio, 0, ',', '.') }}</h4>
                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-sm btn-outline-danger">
                            Ver más
                        </a>
                    </div>
                    
                    @if($producto->stock > 0)
                        <small class="text-success">
                            <i class="bi bi-check-circle"></i> {{ $producto->stock }} disponibles
                        </small>
                    @else
                        <small class="text-danger">
                            <i class="bi bi-x-circle"></i> Sin stock
                        </small>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="text-muted">No hay productos disponibles</p>
        </div>
        @endforelse
    </div>
    
    @if($productosDestacados->count() > 0)
    <div class="text-center mt-4">
        <a href="{{ route('productos.index') }}" class="btn btn-catbox">
            Ver todos los productos <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    @endif
</div>

{{-- Features --}}
<div class="bg-light py-5 mt-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <i class="bi bi-truck display-4 text-danger"></i>
                <h5 class="mt-3">Envío rápido</h5>
                <p class="text-muted">Recibe tus productos en 3-5 días hábiles</p>
            </div>
            <div class="col-md-4 mb-3">
                <i class="bi bi-shield-check display-4 text-danger"></i>
                <h5 class="mt-3">Productos auténticos</h5>
                <p class="text-muted">100% garantía de autenticidad</p>
            </div>
            <div class="col-md-4 mb-3">
                <i class="bi bi-credit-card display-4 text-danger"></i>
                <h5 class="mt-3">Pago seguro</h5>
                <p class="text-muted">PayPal y tarjetas de crédito</p>
            </div>
        </div>
    </div>
</div>
@endsection