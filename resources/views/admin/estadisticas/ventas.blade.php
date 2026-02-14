@extends('layouts.app')
@section('title', 'Análisis de Ventas - Admin')

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

        <div class="col-lg-10 py-4 px-4">
            <h3 class="fw-800 mb-4">Análisis Detallado de Ventas</h3>

            {{-- Filtros avanzados --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Período</label>
                            <select name="periodo" class="form-select">
                                <option value="7" {{ $periodo == 7 ? 'selected' : '' }}>Últimos 7 días</option>
                                <option value="30" {{ $periodo == 30 ? 'selected' : '' }}>Últimos 30 días</option>
                                <option value="90" {{ $periodo == 90 ? 'selected' : '' }}>Últimos 90 días</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-600">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
                        </div>
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
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Métricas principales --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Total Ventas</small>
                            <h3 class="fw-800">${{ number_format($ventasStats['total_ventas'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Promedio por Venta</small>
                            <h3 class="fw-800">${{ number_format($ventasStats['promedio_venta'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Venta Mayor</small>
                            <h3 class="fw-800">${{ number_format($ventasStats['venta_mayor'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Total Órdenes</small>
                            <h3 class="fw-800">{{ number_format($ventasStats['total_ordenes']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráficas --}}
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0">Evolución de Ventas</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="ventasDiariasChart" height="80"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0">Ventas por Categoría</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="categoriasChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0">Ventas por Hora del Día</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="horaChart" height="60"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Ventas por día
const ventasDiarias = @json($ventasPorDia);
new Chart(document.getElementById('ventasDiariasChart'), {
    type: 'line',
    data: {
        labels: ventasDiarias.map(v => new Date(v.fecha).toLocaleDateString('es-ES')),
        datasets: [{
            label: 'Ingresos',
            data: ventasDiarias.map(v => v.ingresos),
            borderColor: '#ff6b6b',
            backgroundColor: 'rgba(255, 107, 107, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: value => '$' + value.toLocaleString() }
            }
        }
    }
});

// Ventas por categoría
const categorias = @json($ventasPorCategoria);
new Chart(document.getElementById('categoriasChart'), {
    type: 'doughnut',
    data: {
        labels: categorias.map(c => c.producto?.categoria?.nombre || 'Sin categoría'),
        datasets: [{
            data: categorias.map(c => c.total_ingresos),
            backgroundColor: ['#667eea', '#f093fb', '#43e97b', '#ff6b6b', '#ffd93d']
        }]
    }
});

// Ventas por hora
const horas = @json($ventasPorHora);
new Chart(document.getElementById('horaChart'), {
    type: 'bar',
    data: {
        labels: horas.map(h => h.hora + ':00'),
        datasets: [{
            label: 'Ventas',
            data: horas.map(h => h.ingresos),
            backgroundColor: '#0d6efd'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: value => '$' + value.toLocaleString() }
            }
        }
    }
});
</script>
@endpush
@endsection