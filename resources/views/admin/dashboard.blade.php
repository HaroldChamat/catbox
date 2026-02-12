@extends('layouts.app')

@section('title', 'Panel de Administración - Catbox')

@section('content')
<div class="container-fluid my-4">
    <div class="row">
        {{-- Sidebar --}}
        <nav class="col-md-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                    <i class="bi bi-shield-lock"></i> ADMINISTRACIÓN
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.productos.index') }}">
                            <i class="bi bi-box-seam"></i> Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.categorias.index') }}">
                            <i class="bi bi-tag"></i> Categorías
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.ordenes.index') }}">
                            <i class="bi bi-receipt"></i> Órdenes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.estadisticas.index') }}">
                            <i class="bi bi-graph-up"></i> Estadísticas
                        </a>
                    </li>
                </ul>

                <hr>

                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('landing') }}">
                            <i class="bi bi-arrow-left"></i> Volver a la tienda
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        {{-- Contenido principal --}}
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </h1>
            </div>

            {{-- Estadísticas principales --}}
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Productos</h6>
                                    <h3 class="mb-0">{{ $stats['total_productos'] }}</h3>
                                </div>
                                <i class="bi bi-box-seam display-4 opacity-50"></i>
                            </div>
                        </div>
                        <a href="{{ route('admin.productos.index') }}" class="card-footer text-white bg-primary bg-opacity-50 text-decoration-none">
                            Ver todos <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Órdenes</h6>
                                    <h3 class="mb-0">{{ $stats['total_ordenes'] }}</h3>
                                </div>
                                <i class="bi bi-receipt display-4 opacity-50"></i>
                            </div>
                        </div>
                        <a href="{{ route('admin.ordenes.index') }}" class="card-footer text-white bg-success bg-opacity-50 text-decoration-none">
                            Ver todas <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-white shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Usuarios</h6>
                                    <h3 class="mb-0">{{ $stats['total_usuarios'] }}</h3>
                                </div>
                                <i class="bi bi-people display-4 opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer text-white bg-warning bg-opacity-50">
                            Total registrados
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card bg-danger text-white shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Ventas del mes</h6>
                                    <h3 class="mb-0">${{ number_format($stats['ventas_mes'], 0, ',', '.') }}</h3>
                                </div>
                                <i class="bi bi-currency-dollar display-4 opacity-50"></i>
                            </div>
                        </div>
                        <a href="{{ route('admin.estadisticas.index') }}" class="card-footer text-white bg-danger bg-opacity-50 text-decoration-none">
                            Ver estadísticas <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Alertas --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>{{ $stats['ordenes_pendientes'] }}</strong> órdenes pendientes de procesar
                        <a href="{{ route('admin.ordenes.index', ['estado' => 'pendiente']) }}" class="alert-link">Ver órdenes</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-danger">
                        <i class="bi bi-box-seam"></i>
                        <strong>{{ $stats['productos_bajo_stock'] }}</strong> productos con stock bajo (menos de 10)
                        <a href="{{ route('admin.productos.index', ['stock_bajo' => 1]) }}" class="alert-link">Ver productos</a>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Órdenes recientes --}}
                <div class="col-lg-7 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history"></i> Órdenes Recientes
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Orden</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ordenesRecientes as $orden)
                                        <tr>
                                            <td><strong>{{ $orden->numero_orden }}</strong></td>
                                            <td>{{ $orden->user->name }}</td>
                                            <td>${{ number_format($orden->total, 0, ',', '.') }}</td>
                                            <td>
                                                @if($orden->estado === 'pendiente')
                                                    <span class="badge bg-warning">Pendiente</span>
                                                @elseif($orden->estado === 'procesando')
                                                    <span class="badge bg-info">Procesando</span>
                                                @elseif($orden->estado === 'enviado')
                                                    <span class="badge bg-primary">Enviado</span>
                                                @elseif($orden->estado === 'entregado')
                                                    <span class="badge bg-success">Entregado</span>
                                                @else
                                                    <span class="badge bg-danger">Cancelado</span>
                                                @endif
                                            </td>
                                            <td>{{ $orden->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay órdenes recientes</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="{{ route('admin.ordenes.index') }}" class="btn btn-sm btn-outline-primary">
                                Ver todas las órdenes <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Top productos --}}
                <div class="col-lg-5 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-trophy"></i> Top 5 Productos
                            </h5>
                        </div>
                        <div class="card-body">
                            @forelse($topProductos as $stat)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0">{{ Str::limit($stat->producto->nombre, 30) }}</h6>
                                    <small class="text-muted">{{ $stat->total_vendido }} vendidos</small>
                                </div>
                                <i class="bi bi-graph-up-arrow text-success"></i>
                            </div>
                            @empty
                            <p class="text-muted text-center">No hay datos disponibles</p>
                            @endforelse
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="{{ route('admin.estadisticas.index') }}" class="btn btn-sm btn-outline-success">
                                Ver estadísticas completas <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

@push('styles')
<style>
    .sidebar {
        position: sticky;
        top: 56px;
        height: calc(100vh - 56px);
        overflow-y: auto;
    }
    .sidebar .nav-link {
        color: #333;
    }
    .sidebar .nav-link.active {
        color: #ff6b6b;
        font-weight: 600;
    }
    .sidebar .nav-link:hover {
        color: #ff6b6b;
    }
</style>
@endpush
@endsection