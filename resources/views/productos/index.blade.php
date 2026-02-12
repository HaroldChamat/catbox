@extends('layouts.app')
@section('title', 'Catálogo - Catbox')

@push('styles')
<style>
    .carousel-productos {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        padding: 60px 0;
        margin-bottom: 40px;
    }
    .carousel-item-custom {
        transition: transform 0.6s ease-in-out;
    }
    .producto-destacado {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    .filter-sidebar {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        position: sticky;
        top: 100px;
    }
    .producto-grid-card {
        border: none;
        border-radius: 16px;
        transition: transform 0.3s, box-shadow 0.3s;
        overflow: hidden;
    }
    .producto-grid-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    .badge-vendido {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>
@endpush

@section('content')

{{-- Carrusel de Productos Más Vendidos --}}
<section class="carousel-productos">
    <div class="container">
        <h2 class="text-white text-center fw-800 mb-4">
            <i class="bi bi-fire text-warning"></i> Los Más Vendidos
        </h2>
        
        <div id="productosDestacadosCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($productosMasVendidos->chunk(3) as $index => $chunk)
                <button type="button" 
                        data-bs-target="#productosDestacadosCarousel" 
                        data-bs-slide-to="{{ $index }}" 
                        class="{{ $index === 0 ? 'active' : '' }}"
                        style="background-color: #ff6b6b;"></button>
                @endforeach
            </div>

            <div class="carousel-inner" style="background: #1a1a2e; border-radius: 20px; padding: 40px 20px;">
                @foreach($productosMasVendidos->chunk(3) as $index => $chunk)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="row g-4 px-3">
                        @foreach($chunk as $producto)
                        <div class="col-md-4">
                            <div class="producto-destacado">
                                <div class="position-relative">
                                    <img src="{{ producto_imagen($producto) }}" 
                                         class="w-100" 
                                         style="height: 300px; object-fit: cover;"
                                         alt="{{ $producto->nombre }}"
                                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                    <span class="position-absolute top-0 start-0 m-3 badge badge-vendido">
                                        <i class="bi bi-star-fill"></i> Más Vendido
                                    </span>
                                </div>
                                <div class="p-4">
                                    <span class="badge bg-secondary mb-2">{{ $producto->categoria->nombre }}</span>
                                    <h5 class="fw-700 mb-2">{{ Str::limit($producto->nombre, 50) }}</h5>
                                    <p class="text-muted small mb-3">{{ Str::limit($producto->descripcion, 80) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="text-danger fw-800 mb-0">${{ number_format($producto->precio, 0, ',', '.') }}</h4>
                                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-catbox">
                                            Ver más <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#productosDestacadosCarousel" data-bs-slide="prev" style="filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));">
                <span class="carousel-control-prev-icon" style="background-color: rgba(0,0,0,0.8); border-radius: 50%; padding: 20px; width: 50px; height: 50px;"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productosDestacadosCarousel" data-bs-slide="next" style="filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));">
                <span class="carousel-control-next-icon" style="background-color: rgba(0,0,0,0.8); border-radius: 50%; padding: 20px; width: 50px; height: 50px;"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
    </div>
</section>

{{-- Catálogo con Filtros --}}
<div class="container mb-5">
    <div class="row">
        
        {{-- Sidebar de Filtros --}}
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar">
                <h5 class="fw-700 mb-4"><i class="bi bi-funnel text-danger me-2"></i>Filtros</h5>
                
                <form action="{{ route('productos.index') }}" method="GET" id="filterForm">
                    
                    {{-- Búsqueda --}}
                    <div class="mb-4">
                        <label class="form-label fw-600 small">Buscar producto</label>
                        <input type="text" 
                               name="buscar" 
                               class="form-control" 
                               placeholder="Nombre del producto..."
                               value="{{ request('buscar') }}">
                    </div>

                    {{-- Categorías --}}
                    <div class="mb-4">
                        <label class="form-label fw-600 small">Categoría</label>
                        <select name="categoria" class="form-select" onchange="this.form.submit()">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ request('categoria') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Rango de Precio --}}
                    <div class="mb-4">
                        <label class="form-label fw-600 small">Precio</label>
                        <select name="precio" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los precios</option>
                            <option value="0-20000" {{ request('precio') == '0-20000' ? 'selected' : '' }}>Menos de $20,000</option>
                            <option value="20000-50000" {{ request('precio') == '20000-50000' ? 'selected' : '' }}>$20,000 - $50,000</option>
                            <option value="50000-100000" {{ request('precio') == '50000-100000' ? 'selected' : '' }}>$50,000 - $100,000</option>
                            <option value="100000-999999" {{ request('precio') == '100000-999999' ? 'selected' : '' }}>Más de $100,000</option>
                        </select>
                    </div>

                    {{-- Disponibilidad --}}
                    <div class="mb-4">
                        <label class="form-label fw-600 small">Disponibilidad</label>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="disponible" 
                                   value="1" 
                                   id="disponible"
                                   {{ request('disponible') ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label" for="disponible">
                                Solo en stock
                            </label>
                        </div>
                    </div>

                    {{-- Ordenar --}}
                    <div class="mb-4">
                        <label class="form-label fw-600 small">Ordenar por</label>
                        <select name="orden" class="form-select" onchange="this.form.submit()">
                            <option value="recientes" {{ request('orden') == 'recientes' ? 'selected' : '' }}>Más recientes</option>
                            <option value="precio_asc" {{ request('orden') == 'precio_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                            <option value="precio_desc" {{ request('orden') == 'precio_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                            <option value="nombre" {{ request('orden') == 'nombre' ? 'selected' : '' }}>Nombre A-Z</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-catbox">
                            <i class="bi bi-search"></i> Aplicar Filtros
                        </button>
                        @if(request()->hasAny(['buscar', 'categoria', 'precio', 'disponible', 'orden']))
                        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid de Productos --}}
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-700 mb-0">
                    <i class="bi bi-grid text-danger me-2"></i>
                    Todos los Productos ({{ $productos->total() }})
                </h4>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" id="gridView">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button class="btn btn-outline-secondary" id="listView">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>

            {{-- Grid de productos --}}
            <div class="row g-4" id="productosGrid">
                @forelse($productos as $producto)
                <div class="col-lg-4 col-md-6 producto-item">
                    <div class="card producto-grid-card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="{{ producto_imagen($producto) }}" 
                                 class="card-img-top" 
                                 style="height: 250px; object-fit: cover;"
                                 alt="{{ $producto->nombre }}"
                                 onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                            
                            {{-- Badges --}}
                            @if($producto->created_at->diffInDays(now()) < 7)
                            <span class="position-absolute top-0 start-0 m-2 badge bg-info">
                                <i class="bi bi-star-fill"></i> Nuevo
                            </span>
                            @endif

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
                            <span class="badge bg-secondary mb-2 align-self-start">{{ $producto->categoria->nombre }}</span>
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
                @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">No se encontraron productos</p>
                    <a href="{{ route('productos.index') }}" class="btn btn-catbox">Ver todos los productos</a>
                </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            @if($productos->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $productos->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Vista de grid/lista
document.getElementById('gridView')?.addEventListener('click', function() {
    document.querySelectorAll('.producto-item').forEach(item => {
        item.className = 'col-lg-4 col-md-6 producto-item';
    });
    this.classList.add('active');
    document.getElementById('listView').classList.remove('active');
});

document.getElementById('listView')?.addEventListener('click', function() {
    document.querySelectorAll('.producto-item').forEach(item => {
        item.className = 'col-12 producto-item';
    });
    this.classList.add('active');
    document.getElementById('gridView').classList.remove('active');
});
</script>
@endpush