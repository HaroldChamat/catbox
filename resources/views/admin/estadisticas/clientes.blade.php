@extends('layouts.app')
@section('title', 'Estadísticas de Clientes - Admin')

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
    .stat-card {
        border-left: 4px solid;
        transition: transform .2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .cliente-card {
        transition: all .2s;
        border-left: 4px solid transparent;
    }
    .cliente-card:hover {
        border-left-color: #0d6efd;
        transform: translateX(5px);
    }
    .cliente-activo {
        background-color: rgba(25, 135, 84, 0.05);
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
                            <li><a class="nav-link small" href="{{ route('admin.estadisticas.productos') }}">Productos</a></li>
                            <li><a class="nav-link active small" href="{{ route('admin.estadisticas.clientes') }}">Clientes</a></li>
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
                    <h3 class="fw-800 mb-1">Análisis de Clientes</h3>
                    <small class="text-muted">Comportamiento y rendimiento de clientes</small>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #0d6efd">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Clientes</small>
                            <h3 class="fw-800 mb-0">{{ number_format($clientesStats['total_clientes']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #198754">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Clientes Nuevos</small>
                            <h3 class="fw-800 mb-0">{{ number_format($clientesStats['clientes_nuevos']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #ffc107">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Clientes Activos</small>
                            <h3 class="fw-800 mb-0">{{ number_format($clientesStats['clientes_activos']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #dc3545">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Ticket Promedio</small>
                            <h3 class="fw-800 mb-0">${{ number_format($clientesStats['ticket_promedio'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.estadisticas.clientes') }}" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Tipo</label>
                            <select name="tipo_cliente" class="form-select">
                                <option value="todos" {{ $tipoCliente == 'todos' ? 'selected' : '' }}>Todos</option>
                                <option value="nuevos" {{ $tipoCliente == 'nuevos' ? 'selected' : '' }}>Nuevos</option>
                                <option value="antiguos" {{ $tipoCliente == 'antiguos' ? 'selected' : '' }}>Antiguos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Ordenar por</label>
                            <select name="ordenar" class="form-select">
                                <option value="mayor_gasto" {{ $ordenar == 'mayor_gasto' ? 'selected' : '' }}>Mayor gasto</option>
                                <option value="menor_gasto" {{ $ordenar == 'menor_gasto' ? 'selected' : '' }}>Menor gasto</option>
                                <option value="mas_ordenes" {{ $ordenar == 'mas_ordenes' ? 'selected' : '' }}>Más órdenes</option>
                                <option value="menos_ordenes" {{ $ordenar == 'menos_ordenes' ? 'selected' : '' }}>Menos órdenes</option>
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
                        <div class="col-md-2">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="solo_activos" id="soloActivos" 
                                       value="1" {{ $soloActivos ? 'checked' : '' }}>
                                <label class="form-check-label small fw-600" for="soloActivos">
                                    Solo activos
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-1">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Top Clientes --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-700 mb-0">Top Clientes por Gasto</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="35%">Cliente</th>
                                    <th class="text-center">Registro</th>
                                    <th class="text-center">Total Órdenes</th>
                                    <th class="text-end">Total Gastado</th>
                                    <th class="text-center" width="15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topClientes as $index => $cliente)
                                <tr class="cliente-card {{ $cliente->total_ordenes > 0 ? 'cliente-activo' : '' }}">
                                    <td class="align-middle text-muted fw-600">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-600">
                                                    {{ $cliente->name }}
                                                    @if($cliente->total_ordenes > 0)
                                                    <span class="badge bg-success badge-sm ms-1">Activo</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">{{ $cliente->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <small>{{ $cliente->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-primary">{{ $cliente->total_ordenes }}</span>
                                    </td>
                                    <td class="text-end align-middle fw-700 text-success">
                                        ${{ number_format($cliente->total_gastado, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('admin.ordenes.index', ['usuario' => $cliente->id, 'desde' => $fechaDesde, 'hasta' => $fechaHasta]) }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ver órdenes de este cliente">
                                            <i class="bi bi-receipt"></i> Ver órdenes
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                        No hay clientes con compras en este período
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Clientes Nuevos --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-700 mb-0">Clientes Nuevos (Priorizando Activos)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th class="text-center">Fecha Registro</th>
                                    <th class="text-center">Órdenes</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientesNuevos as $cliente)
                                <tr class="{{ $cliente->ordenes_count > 0 ? 'cliente-activo' : '' }}">
                                    <td class="align-middle">
                                        <div>
                                            <div class="fw-600">{{ $cliente->name }}</div>
                                            <small class="text-muted">{{ $cliente->email }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <small>{{ $cliente->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge {{ $cliente->ordenes_count > 0 ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $cliente->ordenes_count }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($cliente->ordenes_count > 0)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Activo
                                        </span>
                                        @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-clock"></i> Sin compras
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('admin.ordenes.index', ['usuario' => $cliente->id, 'desde' => $fechaDesde, 'hasta' => $fechaHasta]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay clientes nuevos en este período</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection