@extends('layouts.app')
@section('title', 'Análisis de Productos - Admin')

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
    .producto-row {
        transition: all 0.2s;
    }
    .producto-row:hover {
        background: #f8f9fa;
        transform: scale(1.01);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-2 d-none d-lg-block admin-sidebar pt-4">
            <p class="text-white-50 small px-3 fw-600 text-uppercase mb-2">Menú Admin</p>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="{{ route('admin.productos.index') }}"><i class="bi bi-box-seam me-2"></i>Productos</a></li>
                <li><a class="nav-link" href="{{ route('admin.categorias.index') }}"><i class="bi bi-tag me-2"></i>Categorías</a></li>
                <li><a class="nav-link" href="{{ route('admin.ordenes.index') }}"><i class="bi bi-receipt me-2"></i>Órdenes</a></li>
                <li><a class="nav-link active" href="{{ route('admin.estadisticas.productos') }}"><i class="bi bi-graph-up me-2"></i>Estadísticas</a></li>
            </ul>
        </div>

        <div class="col-lg-10 py-4 px-4">
            <h3 class="fw-800 mb-4">Análisis de Productos</h3>

            {{-- Filtros --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Categoría</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Todas</option>
                                @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ $categoriaId == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Ordenar por</label>
                            <select name="ordenar" class="form-select">
                                <option value="mas_vendidos" {{ $ordenar == 'mas_vendidos' ? 'selected' : '' }}>Más Vendidos</option>
                                <option value="mas_ingresos" {{ $ordenar == 'mas_ingresos' ? 'selected' : '' }}>Más Ingresos</option>
                                <option value="menos_stock" {{ $ordenar == 'menos_stock' ? 'selected' : '' }}>Menos Stock</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Mostrar</label>
                            <select name="limite" class="form-select">
                                <option value="10" {{ $limite == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ $limite == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ $limite == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ $limite == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel"></i> Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Productos más vendidos --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="fw-700 mb-0">
                        Top {{ $limite }} Productos 
                        @if($ordenar == 'mas_vendidos') (Por Ventas)
                        @elseif($ordenar == 'mas_ingresos') (Por Ingresos)
                        @else (Por Stock Bajo)
                        @endif
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Vendidos</th>
                                    <th class="text-center">Órdenes</th>
                                    <th class="text-end">Ingresos</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productos as $index => $item)
                                <tr class="producto-row">
                                    <td class="fw-700">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ producto_imagen($item->producto) }}" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                                                 onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                            <div>
                                                <div class="fw-600">{{ Str::limit($item->producto->nombre, 40) }}</div>
                                                <small class="text-muted">${{ number_format($item->producto->precio, 0, ',', '.') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $item->producto->categoria->nombre }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($item->producto->stock < 5)
                                            <span class="badge bg-danger">{{ $item->producto->stock }}</span>
                                        @elseif($item->producto->stock < 10)
                                            <span class="badge bg-warning">{{ $item->producto->stock }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $item->producto->stock }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-700">{{ $item->total_vendido }}</td>
                                    <td class="text-center">{{ $item->ordenes_count ?? 0 }}</td>
                                    <td class="text-end fw-700 text-success">
                                        ${{ number_format($item->total_ingresos, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.productos.editar', $item->producto->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No hay datos</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Productos con bajo stock --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm border-warning border-start border-4">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0 text-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>Productos con Bajo Stock
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($productosBajoStock as $prod)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ producto_imagen($prod) }}" 
                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;"
                                                 onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                            <div>
                                                <div class="fw-600 small">{{ Str::limit($prod->nombre, 30) }}</div>
                                                <small class="text-muted">{{ $prod->categoria->nombre }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge {{ $prod->stock == 0 ? 'bg-danger' : 'bg-warning' }}">
                                                {{ $prod->stock }} unidades
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="list-group-item text-center text-muted py-4">
                                    No hay productos con bajo stock
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Productos sin ventas --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm border-info border-start border-4">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0 text-info">
                                <i class="bi bi-inbox me-2"></i>Productos Sin Ventas (Período)
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($productosSinVentas as $prod)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ producto_imagen($prod) }}" 
                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;"
                                                 onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                            <div>
                                                <div class="fw-600 small">{{ Str::limit($prod->nombre, 30) }}</div>
                                                <small class="text-muted">{{ $prod->categoria->nombre }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">Stock: {{ $prod->stock }}</small>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="list-group-item text-center text-muted py-4">
                                    ¡Todos los productos han tenido ventas!
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection