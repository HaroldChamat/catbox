@extends('layouts.app')
@section('title', 'Gestión de Categorías - Admin')

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
    .categoria-card {
        border: none;
        border-radius: 16px;
        transition: transform .2s, box-shadow .2s;
    }
    .categoria-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,.1);
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
                <li><a class="nav-link active" href="{{ route('admin.categorias.index') }}"><i class="bi bi-tag me-2"></i>Categorías</a></li>
                <li><a class="nav-link" href="{{ route('admin.ordenes.index') }}"><i class="bi bi-receipt me-2"></i>Órdenes</a></li>
                <li><a class="nav-link" href="{{ route('admin.estadisticas.index') }}"><i class="bi bi-graph-up me-2"></i>Estadísticas</a></li>
            </ul>
        </div>

        {{-- Contenido --}}
        <div class="col-lg-10 py-4 px-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-800 mb-0">Gestión de Categorías</h3>
                    <small class="text-muted">{{ $categorias->count() }} categorías registradas</small>
                </div>
                <a href="{{ route('admin.categorias.crear') }}" class="btn btn-admin px-4">
                    <i class="bi bi-plus-circle me-1"></i> Nueva categoría
                </a>
            </div>

            <div class="row g-4">
                @forelse($categorias as $categoria)
                <div class="col-md-6 col-lg-4">
                    <div class="card categoria-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-700 mb-1">{{ $categoria->nombre }}</h5>
                                    <small class="text-muted">{{ $categoria->slug }}</small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                            type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.categorias.editar', $categoria->id) }}">
                                                <i class="bi bi-pencil me-2"></i>Editar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('productos.categoria', $categoria->slug) }}" target="_blank">
                                                <i class="bi bi-eye me-2"></i>Ver en tienda
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.categorias.eliminar', $categoria->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar categoría {{ addslashes($categoria->nombre) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Eliminar
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <p class="text-muted small mb-4">
                                {{ $categoria->descripcion ?? 'Sin descripción' }}
                            </p>

                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <div class="display-6 fw-800 text-primary">{{ $categoria->productos_count }}</div>
                                        <small class="text-muted">Productos</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="display-6 fw-800 text-success">{{ $categoria->total_stock ?? 0 }}</div>
                                    <small class="text-muted">Stock Total</small>
                                </div>
                            </div>

                            @if($categoria->productos_count > 0)
                            <div class="mt-3">
                                <a href="{{ route('admin.productos.index', ['categoria_id' => $categoria->id]) }}" 
                                   class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-box-seam me-1"></i>Ver productos
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-tags display-1 text-muted"></i>
                        <h4 class="mt-3">No hay categorías registradas</h4>
                        <p class="text-muted">Crea tu primera categoría para empezar</p>
                        <a href="{{ route('admin.categorias.crear') }}" class="btn btn-admin mt-3">
                            <i class="bi bi-plus-circle me-1"></i> Nueva categoría
                        </a>
                    </div>
                </div>
                @endforelse
            </div>

        </div>
    </div>
</div>
@endsection