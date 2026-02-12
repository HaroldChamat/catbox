@extends('layouts.app')
@section('title', 'Gestión de Productos - Admin')

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
    .filter-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
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
                <li><a class="nav-link active" href="{{ route('admin.productos.index') }}"><i class="bi bi-box-seam me-2"></i>Productos</a></li>
                <li><a class="nav-link" href="{{ route('admin.categorias.index') }}"><i class="bi bi-tag me-2"></i>Categorías</a></li>
                <li><a class="nav-link" href="{{ route('admin.ordenes.index') }}"><i class="bi bi-receipt me-2"></i>Órdenes</a></li>
                <li><a class="nav-link" href="{{ route('admin.estadisticas.index') }}"><i class="bi bi-graph-up me-2"></i>Estadísticas</a></li>
            </ul>
        </div>

        {{-- Contenido --}}
        <div class="col-lg-10 py-4 px-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-800 mb-0">Gestión de Productos</h3>
                    <small class="text-muted">{{ $productos->total() }} productos en total</small>
                </div>
                <a href="{{ route('admin.productos.crear') }}" class="btn btn-admin px-4">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo producto
                </a>
            </div>

            {{-- Filtros --}}
            <div class="filter-card">
                <form method="GET" action="{{ route('admin.productos.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-600 small">Buscar por nombre</label>
                            <input type="text" name="nombre" class="form-control" 
                                   placeholder="Nombre del producto" 
                                   value="{{ request('nombre') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-600 small">Categoría</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Todas las categorías</option>
                                @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-600 small">Estado</label>
                            <select name="activo" class="form-select">
                                <option value="">Todos</option>
                                <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-600 small">Stock bajo</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="stock_bajo" value="1" 
                                       class="form-check-input" id="stockBajo"
                                       {{ request('stock_bajo') ? 'checked' : '' }}>
                                <label class="form-check-label" for="stockBajo">
                                    Menos de 10
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            @if(request()->hasAny(['nombre', 'categoria_id', 'activo', 'stock_bajo']))
                            <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4" style="width:60px">#</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $producto)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $producto->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ producto_imagen($producto) }}"
                                             style="width:50px;height:50px;object-fit:cover;border-radius:10px"
                                             onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                        <div>
                                            <div class="fw-600">{{ Str::limit($producto->nombre, 40) }}</div>
                                            <small class="text-muted">{{ Str::limit($producto->descripcion, 50) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $producto->categoria->nombre }}</span>
                                </td>
                                <td class="fw-700 text-danger">${{ number_format($producto->precio, 0, ',', '.') }}</td>
                                <td>
                                    @if($producto->stock <= 0)
                                        <span class="badge bg-danger">Sin stock</span>
                                    @elseif($producto->stock <= 5)
                                        <span class="badge bg-warning text-dark">{{ $producto->stock }} und.</span>
                                    @else
                                        <span class="badge bg-success">{{ $producto->stock }} und.</span>
                                    @endif
                                </td>
                                <td>
                                    @if($producto->activo)
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Activo</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bi bi-pause-circle me-1"></i>Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('productos.show', $producto->id) }}"
                                           target="_blank"
                                           class="btn btn-outline-secondary" title="Ver en tienda">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.productos.editar', $producto->id) }}"
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.productos.eliminar', $producto->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar el producto {{ addslashes($producto->nombre) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No hay productos registrados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($productos->hasPages())
                <div class="card-footer bg-white border-0 d-flex justify-content-center py-3">
                    {{ $productos->appends(request()->query())->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection