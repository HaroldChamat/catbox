@extends('layouts.app')
@section('title', 'Detalle Devolución - Admin')

@section('content')
<div class="container my-5">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.devoluciones.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h3 class="fw-800 mb-0">Solicitud de Devolución #{{ $devolucion->id }}</h3>
            <small class="text-muted">{{ $devolucion->created_at->format('d/m/Y H:i') }}</small>
        </div>
        <div class="ms-auto">
            @if($devolucion->estado === 'pendiente')
                <span class="badge bg-warning text-dark fs-6">Pendiente</span>
            @elseif($devolucion->estado === 'aprobada')
                <span class="badge bg-success fs-6">Aprobada</span>
            @else
                <span class="badge bg-danger fs-6">Rechazada</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Información de la Devolución</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Usuario:</strong> {{ $devolucion->user->name }}
                        <small class="text-muted">({{ $devolucion->user->email }})</small>
                    </div>
                    <div class="mb-3">
                        <strong>Orden:</strong> 
                        <a href="{{ route('admin.ordenes.show', $devolucion->orden_id) }}" target="_blank">
                            {{ $devolucion->orden->numero_orden }}
                        </a>
                    </div>
                    <div class="mb-3">
                        <strong>Motivo:</strong>
                        <p class="text-muted mt-1 p-3 bg-light rounded">{{ $devolucion->motivo }}</p>
                    </div>

                    @if($devolucion->respuesta_admin)
                    <div class="mb-3">
                        <strong>Respuesta del administrador:</strong>
                        <p class="text-muted mt-1 p-3 bg-light rounded">{{ $devolucion->respuesta_admin }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Productos a Devolver</h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Precio Unit.</th>
                                <th>Cantidad</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($devolucion->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ producto_imagen($item->detalleOrden->producto) }}"
                                             style="width:50px;height:50px;object-fit:cover;border-radius:8px"
                                             onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                        <div>{{ $item->detalleOrden->producto->nombre }}</div>
                                    </div>
                                </td>
                                <td>${{ number_format($item->detalleOrden->precio_unitario, 0, ',', '.') }}</td>
                                <td>{{ $item->cantidad }}</td>
                                <td class="fw-bold">${{ number_format($item->monto, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">TOTAL A REEMBOLSAR:</td>
                                <td class="fw-bold text-danger fs-5">${{ number_format($devolucion->monto_total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($devolucion->estaPendiente())
            {{-- Aprobar --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-check-circle me-2"></i>Aprobar Devolución</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.devoluciones.aprobar', $devolucion->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Mensaje para el usuario (opcional)</label>
                            <textarea name="respuesta_admin" class="form-control" rows="3" 
                                      placeholder="Mensaje opcional..."></textarea>
                        </div>
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-1"></i>
                            Al aprobar se:
                            <ul class="mb-0 mt-1">
                                <li>Devolverá el stock</li>
                                <li>Generará crédito de ${{ number_format($devolucion->monto_total, 0, ',', '.') }}</li>
                            </ul>
                        </div>
                        <button type="submit" class="btn btn-success w-100" 
                                onclick="return confirm('¿Aprobar esta devolución?')">
                            <i class="bi bi-check-lg"></i> Aprobar
                        </button>
                    </form>
                </div>
            </div>

            {{-- Rechazar --}}
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="bi bi-x-circle me-2"></i>Rechazar Devolución</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.devoluciones.rechazar', $devolucion->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Motivo del rechazo <span class="text-danger">*</span></label>
                            <textarea name="respuesta_admin" class="form-control" rows="3" 
                                      placeholder="Explica por qué se rechaza..." required></textarea>
                            <small class="text-muted">Mínimo 10 caracteres</small>
                        </div>
                        <button type="submit" class="btn btn-danger w-100" 
                                onclick="return confirm('¿Rechazar esta devolución?')">
                            <i class="bi bi-x-lg"></i> Rechazar
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success display-1"></i>
                    <h5 class="mt-3">Devolución {{ $devolucion->estado === 'aprobada' ? 'Aprobada' : 'Rechazada' }}</h5>
                    <p class="text-muted">
                        {{ $devolucion->estado === 'aprobada' 
                            ? $devolucion->fecha_aprobacion->format('d/m/Y H:i') 
                            : $devolucion->fecha_rechazo->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection