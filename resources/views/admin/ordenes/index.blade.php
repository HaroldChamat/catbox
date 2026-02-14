@extends('layouts.app')
@section('title', 'Gestión de Órdenes - Admin')

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
    .badge-estado {
        min-width: 90px;
        padding: 6px 12px;
    }
    .filtro-activo {
        background: #e7f3ff;
        border-left: 3px solid #0d6efd;
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
                <li><a class="nav-link active" href="{{ route('admin.ordenes.index') }}"><i class="bi bi-receipt me-2"></i>Órdenes</a></li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#estadisticasMenu">
                        <i class="bi bi-graph-up me-2"></i>Estadísticas <i class="bi bi-chevron-down float-end"></i>
                    </a>
                    <div class="collapse" id="estadisticasMenu">
                        <ul class="nav flex-column ms-3">
                            <li><a class="nav-link small" href="{{ route('admin.estadisticas.index') }}">Dashboard</a></li>
                            <li><a class="nav-link small" href="{{ route('admin.estadisticas.ventas') }}">Ventas</a></li>
                            <li><a class="nav-link small" href="{{ route('admin.estadisticas.productos') }}">Productos</a></li>
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
                    <h3 class="fw-800 mb-1">Gestión de Órdenes</h3>
                    @if($usuarioFiltrado)
                    <small class="text-primary">
                        <i class="bi bi-person-fill"></i> Filtrando por: <strong>{{ $usuarioFiltrado->name }}</strong>
                        <a href="{{ route('admin.ordenes.index') }}" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="bi bi-x"></i> Quitar filtro
                        </a>
                    </small>
                    @else
                    <small class="text-muted">Administración de todas las órdenes</small>
                    @endif
                </div>
            </div>

            {{-- Filtros activos --}}
            @if($usuarioFiltrado || $fechaDesde || $fechaHasta)
            <div class="alert alert-info filtro-activo mb-4" role="alert">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-funnel-fill me-2"></i>
                        <strong>Filtros activos:</strong>
                        @if($usuarioFiltrado)
                        <span class="badge bg-primary ms-2">Cliente: {{ $usuarioFiltrado->name }}</span>
                        @endif
                        @if($fechaDesde)
                        <span class="badge bg-info ms-2">Desde: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}</span>
                        @endif
                        @if($fechaHasta)
                        <span class="badge bg-info ms-2">Hasta: {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.ordenes.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-x-circle"></i> Limpiar filtros
                    </a>
                </div>
            </div>
            @endif

            {{-- Filtros --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        @if($usuarioFiltrado)
                        <input type="hidden" name="usuario" value="{{ $usuarioFiltrado->id }}">
                        @endif
                        
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="procesando" {{ request('estado') == 'procesando' ? 'selected' : '' }}>Procesando</option>
                                <option value="enviado" {{ request('estado') == 'enviado' ? 'selected' : '' }}>Enviado</option>
                                <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Desde</label>
                            <input type="date" name="desde" class="form-control" value="{{ $fechaDesde ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Hasta</label>
                            <input type="date" name="hasta" class="form-control" value="{{ $fechaHasta ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Lista de órdenes --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%">ID</th>
                                    <th width="20%">Cliente</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center" width="15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ordenes as $orden)
                                <tr>
                                    <td class="align-middle fw-600">#{{ $orden->id }}</td>
                                    <td class="align-middle">
                                        <div>
                                            <div class="fw-600">{{ $orden->user->name }}</div>
                                            <small class="text-muted">{{ $orden->user->email }}</small>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <small>{{ $orden->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="align-middle text-center">
                                        @php
                                            $estadoClases = [
                                                'pendiente' => 'bg-warning',
                                                'procesando' => 'bg-info',
                                                'enviado' => 'bg-primary',
                                                'entregado' => 'bg-success',
                                                'cancelado' => 'bg-danger'
                                            ];
                                            $clase = $estadoClases[$orden->estado] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge badge-estado {{ $clase }}">
                                            {{ ucfirst($orden->estado) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge bg-light text-dark">{{ $orden->detalles->count() }}</span>
                                    </td>
                                    <td class="align-middle text-end fw-700 text-success">
                                        ${{ number_format($orden->total, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.ordenes.show', $orden->id) }}" 
                                               class="btn btn-outline-primary" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                                                    data-bs-toggle="dropdown">
                                                <span class="visually-hidden">Opciones</span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><h6 class="dropdown-header">Cambiar estado</h6></li>
                                                <li>
                                                    <form method="POST" action="{{ route('admin.ordenes.updateestado', $orden->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="estado" value="procesando">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-clock text-info"></i> Procesando
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" action="{{ route('admin.ordenes.updateestado', $orden->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="estado" value="enviado">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-truck text-primary"></i> Enviado
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" action="{{ route('admin.ordenes.updateestado', $orden->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="estado" value="entregado">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-check-circle text-success"></i> Entregado
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('admin.ordenes.destroy', $orden->id) }}"
                                                          onsubmit="return confirm('¿Estás seguro de eliminar esta orden?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash"></i> Eliminar
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                        <p class="mb-0">No hay órdenes{{ $usuarioFiltrado ? ' para este cliente' : '' }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($ordenes->hasPages())
                <div class="card-footer bg-white border-0">
                    {{ $ordenes->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection