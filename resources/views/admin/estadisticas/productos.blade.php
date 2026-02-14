@extends('layouts.app')
@section('title', 'Estadísticas de Productos - Admin')

@push('styles')
<style>
    .admin-sidebar {
        background: #0f3460;
        min-height: calc(100vh - 56px);
        padding: 20px 0;
    }
    .admin-sidebar .nav-link {
        color: rgba(255,255,255,.7);
        padding: 10px 20px;
        border-radius: 0 30px 30px 0;
        margin-right: 15px;
        transition: all .2s;
        font-weight: 600;
    }
    .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active {
        color: white;
        background: rgba(233,69,96,.3);
    }
    .product-card {
        transition: all .2s;
        border-left: 4px solid transparent;
    }
    .product-card:hover {
        border-left-color: #0d6efd;
        transform: translateX(5px);
    }
    .product-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">

        {{-- Sidebar --}}
        <div class="col-lg-2 d-none d-lg-block admin-sidebar pt-4">
            <p class="text-white-50 small px-3 fw-600 text-uppercase mb-2">Menú Admin</p>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="{{ route('admin.productos.index') }}"><i class="bi bi-box-seam me-2"></i>Productos</a></li>
                <li><a class="nav-link" href="{{ route('admin.categorias.index') }}"><i class="bi bi-tag me-2"></i>Categorías</a></li>
                <li><a class="nav-link" href="{{ route('admin.ordenes.index') }}"><i class="bi bi-receipt me-2"></i>Órdenes</a></li>
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="collapse" href="#estadisticasMenu">
                        <i class="bi bi-graph-up me-2"></i>Estadísticas <i class="bi bi-chevron-down float-end"></i>
                    </a>
                    <div class="collapse show" id="estadisticasMenu">
                        <ul class="nav flex-column ms-3">
                            <li><a class="nav-link small" href="{{ route('admin.estadisticas.index') }}">Dashboard</a></li>
                            <li><a class="nav-link small" href="{{ route('admin.estadisticas.ventas') }}">Ventas</a></li>
                            <li><a class="nav-link active small" href="{{ route('admin.estadisticas.productos') }}">Productos</a></li>
                            <li><a class="nav-link small" href="{{ route('admin.estadisticas.clientes') }}">Clientes</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>

        {{-- Contenido --}}
        <div class="col-lg-10 py-4 px-4">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-800 mb-1">Análisis de Productos</h3>
                    <small class="text-muted">Rendimiento detallado por producto</small>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Categoría</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Todas las categorías</option>
                                @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ $categoriaId == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Ordenar por</label>
                            <select name="ordenar" class="form-select">
                                <option value="mas_vendidos" {{ $ordenar == 'mas_vendidos' ? 'selected' : '' }}>Más vendidos</option>
                                <option value="mas_ingresos" {{ $ordenar == 'mas_ingresos' ? 'selected' : '' }}>Mayores ingresos</option>
                                <option value="mas_ordenes" {{ $ordenar == 'mas_ordenes' ? 'selected' : '' }}>Más órdenes</option>
                                <option value="precio_mayor" {{ $ordenar == 'precio_mayor' ? 'selected' : '' }}>Precio mayor</option>
                                <option value="precio_menor" {{ $ordenar == 'precio_menor' ? 'selected' : '' }}>Precio menor</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Mostrar</label>
                            <select name="limite" class="form-select">
                                <option value="10" {{ $limite == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ $limite == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ $limite == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Productos rendimiento --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-700 mb-0">Productos por Rendimiento</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="40%">Producto</th>
                                    <th class="text-center">Precio</th>
                                    <th class="text-center">Vendidos</th>
                                    <th class="text-center">Órdenes</th>
                                    <th class="text-end">Ingresos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productos as $index => $item)
                                <tr class="product-card">
                                    <td class="align-middle text-muted">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            @if($item->producto->imagenPrincipal)
                                            <img src="{{ asset('storage/' . $item->producto->imagenPrincipal->ruta) }}" 
                                                 alt="{{ $item->producto->nombre }}"
                                                 class="product-img me-3">
                                            @else
                                            <div class="product-img bg-light me-3 d-flex align-items-center justify-content-center">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <div class="fw-600">{{ $item->producto->nombre }}</div>
                                                <small class="text-muted">{{ $item->producto->categoria->nombre ?? 'Sin categoría' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-light text-dark">${{ number_format($item->producto->precio, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-primary">{{ $item->total_vendido }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-info">{{ $item->num_ordenes }}</span>
                                    </td>
                                    <td class="text-end align-middle fw-700 text-success">
                                        ${{ number_format($item->total_ingresos, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                        No hay productos con ventas en este período
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Productos con stock bajo --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0 text-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Stock Bajo
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($productosBajoStock as $producto)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center py-1">
                                                    @if($producto->imagenPrincipal)
                                                    <img src="{{ asset('storage/' . $producto->imagenPrincipal->ruta) }}" 
                                                         style="width: 35px; height: 35px; object-fit: cover;"
                                                         class="rounded me-2">
                                                    @endif
                                                    <div>
                                                        <small class="fw-600">{{ Str::limit($producto->nombre, 30) }}</small>
                                                        <br><small class="text-muted">{{ $producto->categoria->nombre ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge bg-warning">{{ $producto->stock }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="2" class="text-center text-muted py-3">Todos los productos tienen stock adecuado</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Productos sin ventas --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0 text-danger">
                                <i class="bi bi-x-circle-fill me-2"></i>Sin Ventas
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-end">Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($productosSinVentas as $producto)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center py-1">
                                                    @if($producto->imagenPrincipal)
                                                    <img src="{{ asset('storage/' . $producto->imagenPrincipal->ruta) }}" 
                                                         style="width: 35px; height: 35px; object-fit: cover;"
                                                         class="rounded me-2">
                                                    @endif
                                                    <div>
                                                        <small class="fw-600">{{ Str::limit($producto->nombre, 30) }}</small>
                                                        <br><small class="text-muted">{{ $producto->categoria->nombre ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end align-middle">
                                                <small class="fw-600">${{ number_format($producto->precio, 0, ',', '.') }}</small>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="2" class="text-center text-muted py-3">Todos los productos tienen ventas</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection