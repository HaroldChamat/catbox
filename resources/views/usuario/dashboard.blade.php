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
    .direccion-card {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s;
        position: relative;
    }
    .direccion-card.principal {
        border-color: #28a745;
        background: #f0fff4;
    }
    .direccion-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
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

    <div class="row g-4">
        {{-- Órdenes recientes --}}
        <div class="col-lg-6">
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

        {{-- Direcciones de entrega --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="fw-700 mb-0"><i class="bi bi-geo-alt text-danger me-2"></i>Direcciones de entrega</h5>
                    <button class="btn btn-sm btn-catbox" data-bs-toggle="modal" data-bs-target="#nuevaDireccionModal">
                        <i class="bi bi-plus-circle"></i> Agregar
                    </button>
                </div>
                <div class="card-body">
                    @php $direcciones = auth()->user()->direcciones; @endphp
                    
                    @if($direcciones->count() > 0)
                        @foreach($direcciones as $direccion)
                        <div class="direccion-card {{ $direccion->es_principal ? 'principal' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    @if($direccion->es_principal)
                                    <span class="badge bg-success mb-2">
                                        <i class="bi bi-star-fill"></i> Principal
                                    </span>
                                    @endif
                                    <div class="fw-600 mb-1">
                                        <i class="bi bi-house-door me-1"></i>
                                        {{ $direccion->direccion }}
                                    </div>
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $direccion->ciudad }}
                                    </div>
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-mailbox me-1"></i>
                                        CP: {{ $direccion->codigo_postal }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-telephone me-1"></i>
                                        {{ $direccion->telefono }}
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                            type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if(!$direccion->es_principal)
                                        <li>
                                            <form action="{{ route('direcciones.principal', $direccion->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-star text-warning me-2"></i>Establecer como principal
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        <li>
                                            <form action="{{ route('direcciones.eliminar', $direccion->id) }}" 
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar esta dirección?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Eliminar
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-geo-alt display-3 text-muted"></i>
                        <p class="text-muted mt-3 mb-3">No tienes direcciones guardadas</p>
                        <button class="btn btn-catbox" data-bs-toggle="modal" data-bs-target="#nuevaDireccionModal">
                            <i class="bi bi-plus-circle"></i> Agregar primera dirección
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Modal: Nueva Dirección --}}
<div class="modal fade" id="nuevaDireccionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                    Nueva dirección de entrega
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('direcciones.guardar') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Dirección completa <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('direccion') is-invalid @enderror" 
                               name="direccion" 
                               placeholder="Calle, número, apartamento..."
                               value="{{ old('direccion') }}"
                               required>
                        @error('direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">Ciudad <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('ciudad') is-invalid @enderror" 
                               name="ciudad" 
                               placeholder="Tu ciudad"
                               value="{{ old('ciudad') }}"
                               required>
                        @error('ciudad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Código Postal <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('codigo_postal') is-invalid @enderror" 
                                   name="codigo_postal" 
                                   placeholder="12345"
                                   value="{{ old('codigo_postal') }}"
                                   required>
                            @error('codigo_postal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" 
                                   class="form-control @error('telefono') is-invalid @enderror" 
                                   name="telefono" 
                                   placeholder="3001234567"
                                   value="{{ old('telefono') }}"
                                   required>
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Si es tu primera dirección, se establecerá como principal automáticamente.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-catbox">
                        <i class="bi bi-check-circle"></i> Guardar dirección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Reabrir modal si hay errores de validación
@if($errors->any() && (old('direccion') || old('ciudad') || old('codigo_postal') || old('telefono')))
    var modal = new bootstrap.Modal(document.getElementById('nuevaDireccionModal'));
    modal.show();
@endif
</script>
@endpush