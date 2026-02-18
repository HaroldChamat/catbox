@extends('layouts.app')
@section('title', 'Mis Devoluciones')

@section('content')
<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-arrow-return-left me-2"></i>Mis Devoluciones</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @forelse($devoluciones as $devolucion)
    <div class="card mb-3 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <strong>Orden: {{ $devolucion->orden->numero_orden }}</strong>
                <br>
                <small class="text-muted">Solicitada el {{ $devolucion->created_at->format('d/m/Y H:i') }}</small>
            </div>
            <div>
                @if($devolucion->estado === 'pendiente')
                    <span class="badge bg-warning text-dark">Pendiente</span>
                @elseif($devolucion->estado === 'aprobada')
                    <span class="badge bg-success">Aprobada</span>
                @else
                    <span class="badge bg-danger">Rechazada</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6>Productos a devolver:</h6>
                    <ul class="mb-2">
                        @foreach($devolucion->items as $item)
                        <li>
                            {{ $item->detalleOrden->producto->nombre }} 
                            <span class="text-muted">({{ $item->cantidad }} unidad{{ $item->cantidad > 1 ? 'es' : '' }})</span>
                        </li>
                        @endforeach
                    </ul>

                    <h6 class="mt-3">Motivo:</h6>
                    <p class="text-muted mb-2">{{ $devolucion->motivo }}</p>

                    @if($devolucion->respuesta_admin)
                    <div class="alert alert-{{ $devolucion->estaAprobada() ? 'success' : 'danger' }} mt-3">
                        <strong>Respuesta del administrador:</strong>
                        <p class="mb-0 mt-1">{{ $devolucion->respuesta_admin }}</p>
                    </div>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    <h5 class="text-danger">${{ number_format($devolucion->monto_total, 0, ',', '.') }}</h5>
                    
                    @if($devolucion->estaAprobada() && $devolucion->credito)
                    <div class="mt-3">
                        <span class="badge bg-success">Crédito generado</span>
                        <p class="small text-muted mb-0 mt-1">
                            Saldo disponible: ${{ number_format($devolucion->credito->saldo, 0, ',', '.') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <i class="bi bi-arrow-return-left display-1 text-muted"></i>
        <h3 class="mt-3">No tienes solicitudes de devolución</h3>
        <p class="text-muted">Aquí aparecerán tus solicitudes de devolución</p>
    </div>
    @endforelse

    {{ $devoluciones->links() }}
</div>
@endsection