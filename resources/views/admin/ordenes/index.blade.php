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
    .stats-card {
        border-left: 4px solid;
        transition: transform .2s;
    }
    .stats-card:hover {
        transform: translateX(5px);
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
                <li><a class="nav-link" href="{{ route('admin.estadisticas.index') }}"><i class="bi bi-graph-up me-2"></i>Estadísticas</a></li>
            </ul>
        </div>

        {{-- Contenido --}}
        <div class="col-lg-10 py-4 px-4">

            <h3 class="fw-800 mb-4">Gestión de Órdenes</h3>

            {{-- Estadísticas rápidas --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stats-card shadow-sm" style="border-left-color: #0d6efd">
                        <div class="card-body">
                            <small class="text-muted d-block">Total Órdenes</small>
                            <h3 class="fw-800 mb-0">{{ $stats['total_ordenes'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card shadow-sm" style="border-left-color: #ffc107">
                        <div class="card-body">
                            <small class="text-muted d-block">Pendientes</small>
                            <h3 class="fw-800 mb-0">{{ $stats['ordenes_pendientes'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card shadow-sm" style="border-left-color: #198754">
                        <div class="card-body">
                            <small class="text-muted d-block">Completadas</small>
                            <h3 class="fw-800 mb-0">{{ $stats['ordenes_completadas'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card shadow-sm" style="border-left-color: #dc3545">
                        <div class="card-body">
                            <small class="text-muted d-block">Total Ventas</small>
                            <h3 class="fw-800 mb-0">${{ number_format($stats['total_ventas'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    {{-- Filtros --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <form method="GET">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-600">Usuario</label>
                                        <select name="usuario_id" class="form-select">
                                            <option value="">Todos los usuarios</option>
                                            @foreach($usuarios as $user)
                                            <option value="{{ $user->id }}" {{ request('usuario_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-600">Estado</label>
                                        <select name="estado" class="form-select">
                                            <option value="">Todos</option>
                                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="procesando" {{ request('estado') == 'procesando' ? 'selected' : '' }}>Procesando</option>
                                            <option value="enviado" {{ request('estado') == 'enviado' ? 'selected' : '' }}>Enviado</option>
                                            <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                            <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-600">Desde</label>
                                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-600">Hasta</label>
                                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-search"></i> Filtrar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Lista de órdenes --}}
                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>N° Orden</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ordenes as $orden)
                                    <tr>
                                        <td class="fw-600">{{ $orden->numero_orden }}</td>
                                        <td>{{ $orden->user->name }}</td>
                                        <td>{{ $orden->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="fw-700 text-danger">${{ number_format($orden->total, 0, ',', '.') }}</td>
                                        <td>
                                            <form action="{{ route('admin.ordenes.cambiar-estado', $orden->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto">
                                                    <option value="pendiente" {{ $orden->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="procesando" {{ $orden->estado == 'procesando' ? 'selected' : '' }}>Procesando</option>
                                                    <option value="enviado" {{ $orden->estado == 'enviado' ? 'selected' : '' }}>Enviado</option>
                                                    <option value="entregado" {{ $orden->estado == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                                    <option value="cancelado" {{ $orden->estado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.ordenes.show', $orden->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No hay órdenes</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($ordenes->hasPages())
                        <div class="card-footer bg-white">
                            {{ $ordenes->appends(request()->query())->links() }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Usuarios más recurrentes --}}
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0"><i class="bi bi-trophy text-warning me-2"></i>Top Clientes</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($usuariosRecurrentes as $usuario)
                                <a href="{{ route('admin.ordenes.index', ['usuario_id' => $usuario->id]) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-600 small">{{ Str::limit($usuario->name, 20) }}</div>
                                            <small class="text-muted">{{ $usuario->ordenes_count }} órdenes</small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">{{ $usuario->ordenes_count }}</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection