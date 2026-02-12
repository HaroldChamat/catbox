@extends('layouts.app')
@section('title', 'Estadísticas - Admin')

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
    .stat-box {
        border-left: 4px solid;
        transition: transform .2s;
    }
    .stat-box:hover {
        transform: translateY(-3px);
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
                <li><a class="nav-link active" href="{{ route('admin.estadisticas.index') }}"><i class="bi bi-graph-up me-2"></i>Estadísticas</a></li>
            </ul>
        </div>

        {{-- Contenido --}}
        <div class="col-lg-10 py-4 px-4">

            <h3 class="fw-800 mb-4">Estadísticas de Ventas</h3>

            {{-- Filtros --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" id="formFiltros">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-600">Categoría</label>
                                <select name="categoria_id" class="form-select" id="categoriaSelect">
                                    <option value="">Todas las categorías</option>
                                    @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoriaId == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-600">Producto</label>
                                <select name="producto_id" class="form-select" id="productoSelect">
                                    <option value="">Todos los productos</option>
                                    @foreach($productos as $prod)
                                    <option value="{{ $prod->id }}" {{ $productoId == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->nombre }}
                                    </option>
                                    @endforeach
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
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-box shadow-sm" style="border-left-color: #0d6efd">
                        <div class="card-body">
                            <small class="text-muted d-block">Total Ventas</small>
                            <h4 class="fw-800 mb-0">${{ number_format($stats['total_ventas'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-box shadow-sm" style="border-left-color: #198754">
                        <div class="card-body">
                            <small class="text-muted d-block">Órdenes</small>
                            <h4 class="fw-800 mb-0">{{ number_format($stats['total_ordenes']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-box shadow-sm" style="border-left-color: #ffc107">
                        <div class="card-body">
                            <small class="text-muted d-block">Productos Vendidos</small>
                            <h4 class="fw-800 mb-0">{{ number_format($stats['productos_vendidos']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-box shadow-sm" style="border-left-color: #dc3545">
                        <div class="card-body">
                            <small class="text-muted d-block">Ticket Promedio</small>
                            <h4 class="fw-800 mb-0">${{ number_format($stats['ticket_promedio'] ?? 0, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Gráfica de ventas por día --}}
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="fw-700 mb-0">Ventas Últimos 30 Días</h6>
                            <small class="text-muted">Actualización automática cada 5 min</small>
                        </div>
                        <div class="card-body">
                            <canvas id="ventasDiariasChart" height="80"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Top productos --}}
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0">Top 10 Productos</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($productosVendidos as $pv)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="fw-600 small">{{ Str::limit($pv->producto->nombre, 25) }}</div>
                                            <small class="text-muted">{{ $pv->total_vendido }} vendidos</small>
                                        </div>
                                        <span class="badge bg-success">${{ number_format($pv->total_ingresos, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                @empty
                                <div class="list-group-item text-center text-muted py-4">
                                    No hay datos
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ventas por categoría --}}
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0">Ventas por Categoría</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="ventasCategoriaChart" height="60"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Gráfica de ventas diarias
const ventasDiarias = @json($ventasPorDia);
const ctxDiarias = document.getElementById('ventasDiariasChart').getContext('2d');
new Chart(ctxDiarias, {
    type: 'line',
    data: {
        labels: ventasDiarias.map(v => new Date(v.fecha).toLocaleDateString('es-ES', {day: '2-digit', month: 'short'})),
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

// Gráfica de ventas por categoría
const ventasCategoria = @json($ventasPorCategoria);
const ctxCategoria = document.getElementById('ventasCategoriaChart').getContext('2d');
new Chart(ctxCategoria, {
    type: 'bar',
    data: {
        labels: ventasCategoria.map(v => v.producto?.categoria?.nombre || 'Sin categoría'),
        datasets: [{
            label: 'Ingresos',
            data: ventasCategoria.map(v => v.total_ingresos),
            backgroundColor: ['#667eea', '#f093fb', '#43e97b', '#ff6b6b', '#ffd93d']
        }]
    },
    options: {
        responsive: true,
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

// Actualización automática cada 5 minutos
setInterval(() => {
    window.location.reload();
}, 300000);

// Filtro dinámico de productos por categoría
document.getElementById('categoriaSelect').addEventListener('change', function() {
    const categoriaId = this.value;
    if (categoriaId) {
        fetch(`{{ route('admin.estadisticas.index') }}?categoria_id=${categoriaId}`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const productoSelect = doc.getElementById('productoSelect');
                document.getElementById('productoSelect').innerHTML = productoSelect.innerHTML;
            });
    } else {
        document.getElementById('productoSelect').innerHTML = '<option value="">Todos los productos</option>';
    }
});
</script>
@endpush
@endsection