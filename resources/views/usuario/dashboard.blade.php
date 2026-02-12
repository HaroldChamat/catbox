@extends('layouts.app')
@section('title', 'Mi cuenta - Catbox')

@push('styles')
<style>
    .dash-hero {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        padding: 50px 0 70px;
        clip-path: ellipse(100% 80% at 50% 0%);
    }
    .stat-card { border: none; border-radius: 16px; transition: transform .25s; }
    .stat-card:hover { transform: translateY(-5px); }
    .orden-badge { font-size: .75rem; padding: 5px 10px; border-radius: 20px; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="dash-hero text-white mb-n4">
    <div class="container">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center"
                 style="width:60px;height:60px">
                <i class="bi bi-person-fill fs-3"></i>
            </div>
            <div>
                <h2 class="fw-800 mb-0">Hola, {{ auth()->user()->name }} 👋</h2>
                <p class="opacity-75 mb-0">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5" style="margin-top: 50px">

    {{-- Stats --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card stat-card shadow-sm p-4 border-start border-primary border-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-bag-check fs-3 text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-800 text-primary">{{ $totalOrdenes }}</div>
                        <small class="text-muted">Órdenes realizadas</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm p-4 border-start border-success border-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-cash-stack fs-3 text-success"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-800 text-success">${{ number_format($totalGastado, 0, ',', '.') }}</div>
                        <small class="text-muted">Total invertido</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm p-4 border-start border-danger border-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-cart3 fs-3 text-danger"></i>
                    </div>
                    <div>
                        @php $itemsCarrito = auth()->user()->carrito ? auth()->user()->carrito->totalProductos() : 0; @endphp
                        <div class="fs-2 fw-800 text-danger">{{ $itemsCarrito }}</div>
                        <small class="text-muted">En tu carrito</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Acciones rápidas --}}
    <div class="row g-3 mb-5">
        <div class="col-md-3 col-6">
            <a href="{{ route('productos.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-center p-4 h-100 stat-card">
                    <i class="bi bi-shop display-5 text-primary mb-2"></i>
                    <span class="fw-600 small">Ir a la tienda</span>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('carrito.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-center p-4 h-100 stat-card">
                    <i class="bi bi-cart3 display-5 text-success mb-2"></i>
                    <span class="fw-600 small">Mi carrito</span>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('ordenes.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-center p-4 h-100 stat-card">
                    <i class="bi bi-clock-history display-5 text-warning mb-2"></i>
                    <span class="fw-600 small">Mis órdenes</span>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('productos.categoria', 'nendoroid') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-center p-4 h-100 stat-card">
                    <i class="bi bi-star display-5 text-danger mb-2"></i>
                    <span class="fw-600 small">Novedades</span>
                </div>
            </a>
        </div>
    </div>

    {{-- Órdenes recientes --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
            <h5 class="fw-700 mb-0"><i class="bi bi-bag text-danger me-2"></i>Órdenes recientes</h5>
            <a href="{{ route('ordenes.index') }}" class="btn btn-sm btn-outline-danger">Ver todas</a>
        </div>
        <div class="card-body p-0">
            @if($ordenesRecientes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">N° Orden</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ordenesRecientes as $orden)
                        <tr>
                            <td class="ps-4 fw-600">{{ $orden->numero_orden }}</td>
                            <td class="text-muted small">{{ $orden->created_at->format('d/m/Y') }}</td>
                            <td class="fw-700 text-danger">${{ number_format($orden->total, 0, ',', '.') }}</td>
                            <td>
                                @php
                                $badges = [
                                    'pendiente'   => 'bg-warning text-dark',
                                    'procesando'  => 'bg-info',
                                    'enviado'     => 'bg-primary',
                                    'entregado'   => 'bg-success',
                                    'cancelado'   => 'bg-danger',
                                ];
                                $clase = $badges[$orden->estado] ?? 'bg-secondary';
                                @endphp
                                <span class="badge orden-badge {{ $clase }}">
                                    {{ ucfirst($orden->estado) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-bag-x display-3 text-muted"></i>
                <p class="text-muted mt-3">Aún no tienes órdenes</p>
                <a href="{{ route('productos.index') }}" class="btn btn-catbox">Explorar productos</a>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection