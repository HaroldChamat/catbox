@extends('layouts.app')
@section('title', 'Análisis de Clientes - Admin')

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
                <li><a class="nav-link active" href="{{ route('admin.estadisticas.clientes') }}"><i class="bi bi-graph-up me-2"></i>Estadísticas</a></li>
            </ul>
        </div>

        <div class="col-lg-10 py-4 px-4">
            <h3 class="fw-800 mb-4">Análisis de Clientes</h3>

            {{-- Filtros --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-600">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-600">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-600">Mostrar</label>
                            <select name="limite" class="form-select">
                                <option value="10" {{ $limite == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ $limite == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ $limite == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Métricas --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Total Clientes</small>
                            <h3 class="fw-800">{{ number_format($clientesStats['total_clientes']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Clientes Nuevos</small>
                            <h3 class="fw-800 text-success">{{ number_format($clientesStats['clientes_nuevos']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Clientes Activos</small>
                            <h3 class="fw-800 text-primary">{{ number_format($clientesStats['clientes_activos']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Ticket Promedio</small>
                            <h3 class="fw-800 text-warning">${{ number_format($clientesStats['ticket_promedio'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Top clientes --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0">Top Clientes por Gasto</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th>Email</th>
                                            <th class="text-center">Órdenes</th>
                                            <th class="text-end">Total Gastado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topClientes as $index => $cliente)
                                        <tr>
                                            <td class="fw-700">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width:35px;height:35px">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <span class="fw-600">{{ $cliente->name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-muted small">{{ $cliente->email }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $cliente->total_ordenes }}</span>
                                            </td>
                                            <td class="text-end fw-700 text-success">
                                                ${{ number_format($cliente->total_gastado, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No hay datos</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Clientes nuevos --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0">Nuevos Registros</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($clientesNuevos as $cliente)
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                             style="width:30px;height:30px;font-size:.8rem">
                                            <i class="bi bi-person-plus"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-600 small">{{ $cliente->name }}</div>
                                            <small class="text-muted">{{ $cliente->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="list-group-item text-center text-muted py-4">
                                    No hay nuevos registros
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