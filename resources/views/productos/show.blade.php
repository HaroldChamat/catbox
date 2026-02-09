@extends('layouts.app')

@section('title', $producto->nombre . ' - Catbox')

@section('content')
<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('productos.categoria', $producto->categoria->slug) }}">{{ $producto->categoria->nombre }}</a></li>
            <li class="breadcrumb-item active">{{ $producto->nombre }}</li>
        </ol>
    </nav>

    <div class="row">
        {{-- Galería de imágenes --}}
        <div class="col-md-6">
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded shadow">
                    @forelse($producto->imagenes as $index => $imagen)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ asset('storage/' . $imagen->ruta) }}" 
                             class="d-block w-100" 
                             alt="{{ $producto->nombre }}"
                             style="height: 400px; object-fit: cover;">
                    </div>
                    @empty
                    <div class="carousel-item active">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                            <i class="bi bi-image text-muted display-1"></i>
                        </div>
                    </div>
                    @endforelse
                </div>
                
                @if($producto->imagenes->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
                @endif
            </div>
            
            {{-- Miniaturas --}}
            @if($producto->imagenes->count() > 1)
            <div class="row mt-3 g-2">
                @foreach($producto->imagenes as $imagen)
                <div class="col-3">
                    <img src="{{ asset('storage/' . $imagen->ruta) }}" 
                         class="img-thumbnail" 
                         style="cursor: pointer; height: 80px; object-fit: cover; width: 100%;">
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Información del producto --}}
        <div class="col-md-6">
            <span class="badge bg-secondary mb-2">{{ $producto->categoria->nombre }}</span>
            <h1 class="mb-3">{{ $producto->nombre }}</h1>
            
            <div class="mb-4">
                <h2 class="text-danger">${{ number_format($producto->precio, 0, ',', '.') }}</h2>
            </div>

            <div class="mb-4">
                <h5>Descripción</h5>
                <p class="text-muted">{{ $producto->descripcion }}</p>
            </div>

            <div class="mb-4">
                <h5>Disponibilidad</h5>
                @if($producto->stock > 0)
                    <p class="text-success">
                        <i class="bi bi-check-circle"></i> 
                        <strong>{{ $producto->stock }}</strong> unidades disponibles
                    </p>
                @else
                    <p class="text-danger">
                        <i class="bi bi-x-circle"></i> Producto agotado
                    </p>
                @endif
            </div>

            @auth
                @if($producto->stock > 0)
                <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST">
                    @csrf
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" 
                                   name="cantidad" 
                                   class="form-control" 
                                   value="1" 
                                   min="1" 
                                   max="{{ $producto->stock }}" 
                                   required>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-catbox btn-lg">
                            <i class="bi bi-cart-plus"></i> Agregar al carrito
                        </button>
                    </div>
                </form>
                @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Este producto está agotado
                </div>
                @endif
            @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                <a href="{{ route('login') }}">Inicia sesión</a> para agregar productos al carrito
            </div>
            @endauth
        </div>
    </div>

    {{-- Productos relacionados --}}
    @if($relacionados->count() > 0)
    <div class="mt-5">
        <h3 class="mb-4">Productos relacionados</h3>
        <div class="row g-4">
            @foreach($relacionados as $relacionado)
            <div class="col-md-3">
                <div class="card card-product shadow-sm h-100">
                    @if($relacionado->imagenPrincipal)
                        <img src="{{ asset('storage/' . $relacionado->imagenPrincipal->ruta) }}" 
                             class="card-img-top" 
                             alt="{{ $relacionado->nombre }}"
                             style="height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="bi bi-image text-muted display-4"></i>
                        </div>
                    @endif
                    
                    <div class="card-body">
                        <h5 class="card-title">{{ Str::limit($relacionado->nombre, 30) }}</h5>
                        <h4 class="text-danger">${{ number_format($relacionado->precio, 0, ',', '.') }}</h4>
                        <a href="{{ route('productos.show', $relacionado->id) }}" class="btn btn-sm btn-outline-danger w-100">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection