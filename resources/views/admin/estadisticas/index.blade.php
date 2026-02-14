@extends('layouts.app')
@section('title', 'Dashboard de Estadísticas - Admin')

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
    .chart-container {
        position: relative;
        height: 300px;
    }
    .comparison-badge {
        font-size: .9rem;
        padding: 6px 12px;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <li><a class="nav-link active small" href="{{ route('admin.estadisticas.index') }}">Dashboard</a></li>
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
                    <h3 class="fw-800 mb-1">Dashboard de Estadísticas</h3>
                    <small class="text-muted">Vista general del rendimiento</small>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.estadisticas.ventas') }}" class="btn btn-outline-primary btn-sm">Ventas</a>
                    <a href="{{ route('admin.estadisticas.productos') }}" class="btn btn-outline-success btn-sm">Productos</a>
                    <a href="{{ route('admin.estadisticas.clientes') }}" class="btn btn-outline-warning btn-sm">Clientes</a>
                </div>
            </div>

            {{-- Filtro de fechas --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-600">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-600">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #0d6efd">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Ventas</small>
                            <h3 class="fw-800 mb-2">${{ number_format($stats['total_ventas'], 0, ',', '.') }}</h3>
                            @if($comparison['cambio_porcentaje'] != 0)
                            <span class="comparison-badge badge {{ $comparison['cambio_porcentaje'] > 0 ? 'bg-success' : 'bg-danger' }}">
                                <i class="bi bi-{{ $comparison['cambio_porcentaje'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($comparison['cambio_porcentaje']) }}%
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #198754">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Órdenes</small>
                            <h3 class="fw-800 mb-0">{{ number_format($stats['total_ordenes']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #ffc107">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Ticket Promedio</small>
                            <h3 class="fw-800 mb-0">${{ number_format($stats['ticket_promedio'] ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #dc3545">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Productos Vendidos</small>
                            <h3 class="fw-800 mb-0">{{ number_format($stats['productos_vendidos']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Gráfica de ventas --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0">Ventas Últimos 30 Días</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="ventasChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Distribución por estado --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0">Estados de Órdenes</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="estadosChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Top productos --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <h6 class="fw-700 mb-0">Top 5 Productos</h6>
                            <a href="{{ route('admin.estadisticas.productos') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Vendidos</th>
                                            <th class="text-end">Ingresos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topProductos as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ producto_imagen($item->producto) }}" 
                                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;"
                                                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                                    <div>
                                                        <div class="fw-600 small">{{ Str::limit($item->producto->nombre, 30) }}</div>
                                                        <small class="text-muted">{{ $item->producto->categoria->nombre }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $item->total_vendido }}</span>
                                            </td>
                                            <td class="text-end fw-700 text-success">
                                                ${{ number_format($item->total_ingresos, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No hay datos</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Accesos rápidos --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0">Análisis Detallado</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.estadisticas.ventas') }}" class="btn btn-outline-primary text-start">
                                    <i class="bi bi-currency-dollar me-2"></i>
                                    Análisis de Ventas
                                    <i class="bi bi-chevron-right float-end"></i>
                                </a>
                                <a href="{{ route('admin.estadisticas.productos') }}" class="btn btn-outline-success text-start">
                                    <i class="bi bi-box-seam me-2"></i>
                                    Rendimiento de Productos
                                    <i class="bi bi-chevron-right float-end"></i>
                                </a>
                                <a href="{{ route('admin.estadisticas.clientes') }}" class="btn btn-outline-warning text-start">
                                    <i class="bi bi-people me-2"></i>
                                    Análisis de Clientes
                                    <i class="bi bi-chevron-right float-end"></i>
                                </a>
                                <a href="{{ route('admin.ordenes.index') }}" class="btn btn-outline-info text-start">
                                    <i class="bi bi-receipt me-2"></i>
                                    Gestión de Órdenes
                                    <i class="bi bi-chevron-right float-end"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Gráfica de ventas por día
const ventasData = @json($ventasPorDia);
const ctxVentas = document.getElementById('ventasChart').getContext('2d');
new Chart(ctxVentas, {
    type: 'line',
    data: {
        labels: ventasData.map(v => new Date(v.fecha).toLocaleDateString('es-ES', {day: '2-digit', month: 'short'})),
        datasets: [{
            label: 'Ingresos',
            data: ventasData.map(v => v.ingresos),
            borderColor: '#ff6b6b',
            backgroundColor: 'rgba(255, 107, 107, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Gráfica de distribución por estado
const estadosData = @json($distribucionEstados);
const ctxEstados = document.getElementById('estadosChart').getContext('2d');
new Chart(ctxEstados, {
    type: 'doughnut',
    data: {
        labels: estadosData.map(e => e.estado.charAt(0).toUpperCase() + e.estado.slice(1)),
        datasets: [{
            data: estadosData.map(e => e.total),
            backgroundColor: [
                '#ffc107', // pendiente
                '#0dcaf0', // procesando
                '#0d6efd', // enviado
                '#198754', // entregado
                '#dc3545'  // cancelado
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
@endsection