@extends('layouts.app')
@section('title', 'Estadísticas de Ventas - Admin')

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
        height: 350px;
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
                            <li><a class="nav-link small" href="{{ route('admin.estadisticas.index') }}">Dashboard</a></li>
                            <li><a class="nav-link active small" href="{{ route('admin.estadisticas.ventas') }}">Ventas</a></li>
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
                    <h3 class="fw-800 mb-1">Análisis de Ventas</h3>
                    <small class="text-muted">Estadísticas detalladas de ingresos</small>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.estadisticas.ventas') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}" required>
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
                            <label class="form-label small fw-600">Agrupar por</label>
                            <select name="agrupar" class="form-select">
                                <option value="dia" {{ $agrupar == 'dia' ? 'selected' : '' }}>Día</option>
                                <option value="hora" {{ $agrupar == 'hora' ? 'selected' : '' }}>Hora</option>
                                <option value="categoria" {{ $agrupar == 'categoria' ? 'selected' : '' }}>Categoría</option>
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

            {{-- Stats Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #0d6efd">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Ventas</small>
                            <h3 class="fw-800 mb-0">${{ number_format($ventasStats['total_ventas'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #198754">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Órdenes</small>
                            <h3 class="fw-800 mb-0">{{ number_format($ventasStats['total_ordenes']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #ffc107">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Promedio por Venta</small>
                            <h3 class="fw-800 mb-0">${{ number_format($ventasStats['promedio_venta'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm" style="border-left-color: #dc3545">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Venta Mayor</small>
                            <h3 class="fw-800 mb-0">${{ number_format($ventasStats['venta_mayor'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráficas según el agrupamiento --}}
            <div class="row g-4">
                
                @if($agrupar == 'dia')
                {{-- Ventas por Día --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0">Ventas por Día</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="ventasDiaChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($agrupar == 'hora')
                {{-- Ventas por Hora --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0">Ventas por Hora del Día</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="ventasHoraChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($agrupar == 'categoria')
                {{-- Ventas por Categoría --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0">Ventas por Categoría (Gráfico)</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="ventasCategoriaChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0">Detalle por Categoría</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Categoría</th>
                                            <th class="text-center">Productos Vendidos</th>
                                            <th class="text-center">Órdenes</th>
                                            <th class="text-end">Ingresos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ventasPorCategoria as $venta)
                                        <tr>
                                            <td class="fw-600">{{ $venta->nombre ?? 'Sin categoría' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $venta->total_vendido }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $venta->num_ordenes }}</span>
                                            </td>
                                            <td class="text-end fw-700 text-success">
                                                ${{ number_format($venta->total_ingresos, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">No hay datos</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Siempre mostrar ventas por categoría (resumen) --}}
                @if($agrupar != 'categoria')
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0">Ventas por Categoría</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="categoriaResumenChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Ventas por Hora (resumen) --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-700 mb-0">Ventas por Hora</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="horaResumenChart"></canvas>
                        </div>
                    </div>
                </div>
                @endif

            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Datos para los gráficos
const ventasPorDia = @json($ventasPorDia ?? collect());
const ventasPorHora = @json($ventasPorHora ?? collect());
const ventasPorCategoria = @json($ventasPorCategoria ?? collect());
const agrupar = '{{ $agrupar }}';

// Gráfico de Ventas por Día
@if($agrupar == 'dia')
if (document.getElementById('ventasDiaChart')) {
    const ctxDia = document.getElementById('ventasDiaChart').getContext('2d');
    new Chart(ctxDia, {
        type: 'bar',
        data: {
            labels: ventasPorDia.map(v => {
                const fecha = new Date(v.fecha);
                return fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
            }),
            datasets: [{
                label: 'Ingresos',
                data: ventasPorDia.map(v => v.ingresos),
                backgroundColor: 'rgba(13, 110, 253, 0.8)',
                borderColor: '#0d6efd',
                borderWidth: 2
            }, {
                label: 'Órdenes',
                data: ventasPorDia.map(v => v.ordenes),
                backgroundColor: 'rgba(25, 135, 84, 0.8)',
                borderColor: '#198754',
                borderWidth: 2,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: value => '$' + value.toLocaleString()
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.yAxisID === 'y1') {
                                label += context.parsed.y;
                            } else {
                                label += '$' + context.parsed.y.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}
@endif

// Gráfico de Ventas por Hora
@if($agrupar == 'hora')
if (document.getElementById('ventasHoraChart')) {
    const ctxHora = document.getElementById('ventasHoraChart').getContext('2d');
    new Chart(ctxHora, {
        type: 'line',
        data: {
            labels: ventasPorHora.map(v => v.hora + ':00'),
            datasets: [{
                label: 'Ingresos por Hora',
                data: ventasPorHora.map(v => v.ingresos),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '$' + value.toLocaleString()
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: context => '$' + context.parsed.y.toLocaleString()
                    }
                }
            }
        }
    });
}
@endif

// Gráfico de Ventas por Categoría (principal)
@if($agrupar == 'categoria')
if (document.getElementById('ventasCategoriaChart')) {
    const ctxCategoria = document.getElementById('ventasCategoriaChart').getContext('2d');
    new Chart(ctxCategoria, {
        type: 'doughnut',
        data: {
            labels: ventasPorCategoria.map(c => c.nombre || 'Sin categoría'),
            datasets: [{
                data: ventasPorCategoria.map(c => c.total_ingresos),
                backgroundColor: [
                    '#0d6efd', '#198754', '#ffc107', '#dc3545', 
                    '#6f42c1', '#0dcaf0', '#fd7e14', '#20c997'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: context => context.label + ': $' + context.parsed.toLocaleString()
                    }
                }
            }
        }
    });
}
@endif

// Gráficos de resumen (cuando no está en modo específico)
@if($agrupar != 'categoria')
if (document.getElementById('categoriaResumenChart') && ventasPorCategoria.length > 0) {
    const ctxCatRes = document.getElementById('categoriaResumenChart').getContext('2d');
    new Chart(ctxCatRes, {
        type: 'doughnut',
        data: {
            labels: ventasPorCategoria.map(c => c.nombre || 'Sin categoría'),
            datasets: [{
                data: ventasPorCategoria.map(c => c.total_ingresos),
                backgroundColor: [
                    '#0d6efd', '#198754', '#ffc107', '#dc3545', 
                    '#6f42c1', '#0dcaf0', '#fd7e14', '#20c997'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: context => context.label + ': $' + context.parsed.toLocaleString()
                    }
                }
            }
        }
    });
}
@endif

@if($agrupar != 'hora')
if (document.getElementById('horaResumenChart') && ventasPorHora.length > 0) {
    const ctxHoraRes = document.getElementById('horaResumenChart').getContext('2d');
    new Chart(ctxHoraRes, {
        type: 'bar',
        data: {
            labels: ventasPorHora.map(v => v.hora + ':00'),
            datasets: [{
                label: 'Ingresos',
                data: ventasPorHora.map(v => v.ingresos),
                backgroundColor: 'rgba(13, 110, 253, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '$' + value.toLocaleString()
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => '$' + context.parsed.y.toLocaleString()
                    }
                }
            }
        }
    });
}
@endif
</script>
@endpush
@endsection