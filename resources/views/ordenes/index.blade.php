@extends('layouts.app')
@section('title', 'Mis Órdenes - Catbox')

@push('styles')
<style>
    .orden-hero {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        padding: 40px 0;
        color: white;
        margin-bottom: 30px;
    }
    .orden-card {
        border: none;
        border-radius: 16px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .orden-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .estado-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="orden-hero">
    <div class="container">
        <h2 class="fw-800 mb-2">
            <i class="bi bi-bag-check me-2"></i>
            Mis Órdenes
        </h2>
        <p class="mb-0 opacity-75">Historial completo de tus compras</p>
    </div>
</div>

<div class="container pb-5">
    
    {{-- Resumen rápido --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-cart-check fs-3 text-primary"></i>
                    <div>
                        <div class="fs-5 fw-800">{{ $ordenes->total() }}</div>
                        <small class="text-muted">Total órdenes</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history fs-3 text-warning"></i>
                    <div>
                        <div class="fs-5 fw-800">{{ $ordenes->where('estado', 'procesando')->count() }}</div>
                        <small class="text-muted">En proceso</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-truck fs-3 text-info"></i>
                    <div>
                        <div class="fs-5 fw-800">{{ $ordenes->where('estado', 'enviado')->count() }}</div>
                        <small class="text-muted">En camino</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle fs-3 text-success"></i>
                    <div>
                        <div class="fs-5 fw-800">{{ $ordenes->where('estado', 'entregado')->count() }}</div>
                        <small class="text-muted">Entregadas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de órdenes --}}
    @forelse($ordenes as $orden)
    <div class="card orden-card shadow-sm mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="text-center">
                        <i class="bi bi-receipt fs-1 text-danger"></i>
                        <div class="fw-700 mt-2">{{ $orden->numero_orden }}</div>
                        <small class="text-muted">{{ $orden->created_at->format('d/m/Y') }}</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <h6 class="fw-700 mb-2">Productos</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($orden->detalles->take(3) as $detalle)
                        <img src="{{ producto_imagen($detalle->producto) }}" 
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                             alt="{{ $detalle->producto->nombre }}"
                             title="{{ $detalle->producto->nombre }}"
                             onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                        @endforeach
                        @if($orden->detalles->count() > 3)
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" 
                             style="width: 50px; height: 50px;">
                            <span class="fw-700 small">+{{ $orden->detalles->count() - 3 }}</span>
                        </div>
                        @endif
                    </div>
                    <small class="text-muted d-block mt-2">{{ $orden->detalles->count() }} producto(s)</small>
                </div>

                <div class="col-md-2 text-center">
                    <h6 class="fw-700 mb-1">Total</h6>
                    <h4 class="text-danger fw-800 mb-0">${{ number_format($orden->total, 0, ',', '.') }}</h4>
                </div>

                <div class="col-md-2 text-center">
                    <h6 class="fw-700 mb-2">Estado</h6>
                    @php
                    $badges = [
                        'pendiente'   => ['class' => 'bg-warning text-dark', 'icon' => 'clock'],
                        'procesando'  => ['class' => 'bg-info', 'icon' => 'arrow-repeat'],
                        'enviado'     => ['class' => 'bg-primary', 'icon' => 'truck'],
                        'entregado'   => ['class' => 'bg-success', 'icon' => 'check-circle'],
                        'cancelado'   => ['class' => 'bg-danger', 'icon' => 'x-circle'],
                    ];
                    $badge = $badges[$orden->estado] ?? ['class' => 'bg-secondary', 'icon' => 'question'];
                    @endphp
                    <span class="estado-badge {{ $badge['class'] }}">
                        <i class="bi bi-{{ $badge['icon'] }}"></i> {{ ucfirst($orden->estado) }}
                    </span>
                </div>

                <div class="col-md-2 text-end">
                    <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-catbox w-100">
                        <i class="bi bi-eye"></i> Ver detalles
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <i class="bi bi-bag-x display-1 text-muted"></i>
        <h4 class="mt-3">No tienes órdenes aún</h4>
        <p class="text-muted">¡Empieza a comprar y tus órdenes aparecerán aquí!</p>
        <a href="{{ route('productos.index') }}" class="btn btn-catbox mt-3">
            <i class="bi bi-shop"></i> Ir a la tienda
        </a>
    </div>
    @endforelse

    {{-- Paginación --}}
    @if($ordenes->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $ordenes->links() }}
    </div>
    @endif
</div>
@endsection