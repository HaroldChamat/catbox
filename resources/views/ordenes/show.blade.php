@extends('layouts.app')
@section('title', 'Orden #' . $orden->numero_orden . ' - Catbox')

@push('styles')
<style>
    .orden-hero {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        padding: 40px 0;
        color: white;
    }
    .timeline-step {
        position: relative;
        padding: 20px;
        border-left: 3px solid #e0e0e0;
    }
    .timeline-step.active {
        border-left-color: #28a745;
    }
    .timeline-step.current {
        border-left-color: #ff6b6b;
        background: #fff5f5;
    }
    .info-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s;
    }
    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .producto-item {
        border-bottom: 1px solid #f0f0f0;
        padding: 15px 0;
    }
    .producto-item:last-child {
        border-bottom: none;
    }
    .badge-estado {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
    }
    .pago-form {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 12px;
    }
    .comentarios-container {
    scrollbar-width: thin;
}
.comentarios-container::-webkit-scrollbar {
    width: 6px;
}
.comentarios-container::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}
.comentario-item {
    animation: fadeIn 0.3s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="orden-hero mb-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('ordenes.index') }}" class="btn btn-sm btn-outline-light mb-3">
                    <i class="bi bi-arrow-left"></i> Volver a mis órdenes
                </a>
                <h2 class="fw-800 mb-1">Orden {{ $orden->numero_orden }}</h2>
                <p class="mb-0 opacity-75">Creada el {{ $orden->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
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
                <span class="badge badge-estado {{ $badge['class'] }} fs-5">
                    <i class="bi bi-{{ $badge['icon'] }}"></i> {{ ucfirst($orden->estado) }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        
        {{-- Columna izquierda: Productos y Timeline --}}
        <div class="col-lg-8">
            
            {{-- Timeline de estado --}}
            <div class="card info-card shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-700 mb-0"><i class="bi bi-activity text-danger me-2"></i>Estado de tu orden</h5>
                </div>
                <div class="card-body">
                    @php
                    $estados = ['pendiente', 'procesando', 'enviado', 'entregado'];
                    $estadoIndex = array_search($orden->estado, $estados);
                    @endphp
                    
                    @if($orden->estado !== 'cancelado')
                    <div class="d-flex justify-content-between text-center position-relative mb-3">
                        <div class="position-absolute top-50 start-0 w-100" style="height: 3px; background: #e0e0e0; z-index: 0;"></div>
                        <div class="position-absolute top-50 start-0" 
                             style="height: 3px; background: #28a745; width: {{ $estadoIndex !== false ? ($estadoIndex / 3 * 100) : 0 }}%; z-index: 1;"></div>
                        
                        @foreach($estados as $index => $estado)
                        <div class="position-relative" style="z-index: 2; flex: 1;">
                            <div class="mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center 
                                        {{ $index <= $estadoIndex ? 'bg-success' : 'bg-light' }}"
                                 style="width: 50px; height: 50px;">
                                <i class="bi bi-{{ $index == 0 ? 'clock' : ($index == 1 ? 'arrow-repeat' : ($index == 2 ? 'truck' : 'check-circle')) }} 
                                          {{ $index <= $estadoIndex ? 'text-white' : 'text-muted' }} fs-5"></i>
                            </div>
                            <small class="fw-600 {{ $index <= $estadoIndex ? 'text-success' : 'text-muted' }}">
                                {{ ucfirst($estado) }}
                            </small>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-x-circle me-2"></i>
                        <strong>Orden cancelada</strong>
                    </div>
                    @endif
                    
                    @if($orden->fecha_entrega_estimada && !in_array($orden->estado, ['cancelado', 'entregado']))
                    <div class="alert alert-info mb-0 mt-3">
                        <i class="bi bi-calendar-event me-2"></i>
                        <strong>Entrega estimada:</strong> {{ $orden->fecha_entrega_estimada->format('d/m/Y') }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Botón solicitar devolución --}}
            @if($orden->puedeSerDevuelta() && !$orden->tieneSolicitudDevolucionPendiente())
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body text-center">
                    <h6><i class="bi bi-arrow-return-left me-2"></i>¿Problema con tu pedido?</h6>
                    <p class="text-muted small mb-3">Tienes 30 días desde la entrega para solicitar una devolución</p>
                    <a href="{{ route('devoluciones.crear', $orden->id) }}" class="btn btn-outline-danger">
                        Solicitar devolución
                    </a>
                </div>
            </div>
            @elseif($orden->tieneSolicitudDevolucionPendiente())
            <div class="alert alert-warning mt-3">
                <i class="bi bi-clock me-2"></i>Tienes una solicitud de devolución pendiente de revisión
            </div>
            @endif

            {{-- Mostrar devoluciones y sus respuestas --}}
            @if($orden->devoluciones->count() > 0)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-arrow-return-left me-2"></i>Devoluciones</h6>
                </div>
                <div class="card-body">
                    @foreach($orden->devoluciones as $devolucion)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>Solicitud del {{ $devolucion->created_at->format('d/m/Y') }}</strong>
                                <br>
                                <small class="text-muted">Monto: ${{ number_format($devolucion->monto_total, 0, ',', '.') }}</small>
                            </div>
                            @if($devolucion->estado === 'pendiente')
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @elseif($devolucion->estado === 'aprobada')
                                <span class="badge bg-success">Aprobada</span>
                            @else
                                <span class="badge bg-danger">Rechazada</span>
                            @endif
                        </div>

                        @if($devolucion->respuesta_admin)
                        <div class="alert alert-{{ $devolucion->estaAprobada() ? 'success' : 'danger' }} mb-0">
                            <strong>
                                <i class="bi bi-{{ $devolucion->estaAprobada() ? 'check-circle' : 'x-circle' }} me-1"></i>
                                Respuesta del administrador:
                            </strong>
                            <p class="mb-0 mt-1">{{ $devolucion->respuesta_admin }}</p>
                            
                            @if($devolucion->estaAprobada())
                            <hr>
                            <small>
                                <i class="bi bi-wallet2 me-1"></i>
                                Se generó un crédito de ${{ number_format($devolucion->monto_total, 0, ',', '.') }} en tu cuenta.
                                <a href="{{ route('creditos.index') }}" class="alert-link">Ver mis créditos</a>
                            </small>
                            @endif
                        </div>
                        @else
                        <p class="text-muted mb-0"><em>Esperando respuesta del administrador...</em></p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Lista de productos --}}
            <div class="card info-card shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-700 mb-0"><i class="bi bi-bag-check text-danger me-2"></i>Productos ({{ $orden->detalles->count() }})</h5>
                    <h4 class="text-danger fw-800 mb-0">${{ number_format($orden->total, 0, ',', '.') }}</h4>
                </div>
                <div class="card-body">
                    @foreach($orden->detalles as $detalle)
                    <div class="producto-item">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="{{ producto_imagen($detalle->producto) }}" 
                                     class="img-fluid rounded"
                                     style="height: 80px; width: 80px; object-fit: cover;"
                                     onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                            </div>
                            <div class="col-md-5">
                                <h6 class="fw-700 mb-1">{{ $detalle->producto->nombre }}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-tag me-1"></i>{{ $detalle->producto->categoria->nombre }}
                                </small>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge bg-secondary">x{{ $detalle->cantidad }}</span>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="text-muted small">${{ number_format($detalle->precio_unitario, 0, ',', '.') }} c/u</div>
                                <div class="fw-700 text-danger">${{ number_format($detalle->subtotal, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <h5 class="mb-0">TOTAL</h5>
                        <h3 class="text-danger fw-800 mb-0">${{ number_format($orden->total, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna derecha: Info de entrega y pago --}}
        <div class="col-lg-4">
            
            {{-- Dirección de entrega --}}
            <div class="card info-card shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-700 mb-0"><i class="bi bi-geo-alt text-danger me-2"></i>Dirección de entrega</h6>
                    @if(in_array($orden->estado, ['pendiente', 'procesando']))
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#direccionModal">
                        <i class="bi bi-pencil"></i>
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($orden->direccion_id && $orden->direccion)
                    <div class="p-3 bg-light rounded">
                        <p class="mb-2"><i class="bi bi-house-door text-primary me-2"></i><strong>{{ $orden->direccion->direccion }}</strong></p>
                        <p class="mb-2"><i class="bi bi-building text-primary me-2"></i>{{ $orden->direccion->ciudad }}</p>
                        <p class="mb-2"><i class="bi bi-mailbox text-primary me-2"></i>CP: {{ $orden->direccion->codigo_postal }}</p>
                        <p class="mb-0"><i class="bi bi-telephone text-primary me-2"></i>{{ $orden->direccion->telefono }}</p>
                    </div>
                    @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>¡Atención!</strong> No has agregado una dirección de entrega.
                        @if(in_array($orden->estado, ['pendiente', 'procesando']))
                        <button class="btn btn-sm btn-warning mt-2 w-100" data-bs-toggle="modal" data-bs-target="#direccionModal">
                            <i class="bi bi-plus-circle me-1"></i>Agregar ahora
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Información de pago --}}
            <div class="card info-card shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-700 mb-0"><i class="bi bi-credit-card text-danger me-2"></i>Información de pago</h6>
                    @if($orden->estado === 'pendiente' && (!$orden->pago || $orden->pago->estado !== 'completado'))
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#pagoModal">
                        <i class="bi bi-pencil"></i>
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($orden->pago && $orden->pago->estado === 'completado')
                    <div class="p-3 bg-light rounded">
                        <p class="mb-2">
                            <i class="bi bi-{{ $orden->pago->metodo_pago === 'tarjeta' ? 'credit-card' : 'paypal' }} text-success me-2"></i>
                            <strong>{{ ucfirst($orden->pago->metodo_pago) }}</strong>
                        </p>
                        @if($orden->pago->datos_pago && isset($orden->pago->datos_pago['ultimos_digitos']))
                        <p class="mb-2">
                            <i class="bi bi-shield-check text-success me-2"></i>
                            **** **** **** {{ $orden->pago->datos_pago['ultimos_digitos'] }}
                        </p>
                        @endif
                        @if($orden->pago->datos_pago && isset($orden->pago->datos_pago['paypal_email']))
                        <p class="mb-2">
                            <i class="bi bi-envelope text-success me-2"></i>
                            {{ $orden->pago->datos_pago['paypal_email'] }}
                        </p>
                        @endif
                        <p class="mb-2">
                            <i class="bi bi-cash text-success me-2"></i>
                            Monto: <strong class="text-success">${{ number_format($orden->pago->monto, 0, ',', '.') }}</strong>
                        </p>
                        <p class="mb-0">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> {{ ucfirst($orden->pago->estado) }}
                            </span>
                        </p>
                    </div>
                    @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Pago pendiente</strong>
                        @if($orden->estado === 'pendiente')
                        <button class="btn btn-sm btn-warning mt-2 w-100" data-bs-toggle="modal" data-bs-target="#pagoModal">
                            <i class="bi bi-credit-card me-1"></i>Completar pago
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Notas --}}
            <div class="card info-card shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-700 mb-0"><i class="bi bi-chat-left-text text-danger me-2"></i>Notas</h6>
                    @if(in_array($orden->estado, ['pendiente', 'procesando']))
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#notasModal">
                        <i class="bi bi-pencil"></i>
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($orden->notas)
                    <p class="mb-0 fst-italic">{{ $orden->notas }}</p>
                    @else
                    <p class="text-muted mb-0 small">Sin notas adicionales</p>
                    @endif
                </div>
            </div>
            {{-- Comentarios / Chat --}}
            <div class="card info-card shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-700 mb-0">
                        <i class="bi bi-chat-dots text-danger me-2"></i>
                        Mensajes
                        @if($orden->comentariosNoLeidosPara(false) > 0)
                        <span class="badge bg-danger">{{ $orden->comentariosNoLeidosPara(false) }}</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Lista de comentarios --}}
                    <div class="comentarios-container mb-3" style="max-height: 400px; overflow-y: auto;">
                        @forelse($orden->comentarios as $comentario)
                        <div class="comentario-item mb-3 {{ $comentario->es_admin ? 'text-end' : '' }}">
                            <div class="d-inline-block {{ $comentario->es_admin ? 'bg-primary bg-opacity-10 border-primary' : 'bg-light' }} rounded p-3" 
                                style="max-width: 75%; border-left: 3px solid;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @if($comentario->es_admin)
                                    <i class="bi bi-shield-check text-primary"></i>
                                    @else
                                    <i class="bi bi-person-circle text-secondary"></i>
                                    @endif
                                    <strong class="small">{{ $comentario->nombreAutor }}</strong>
                                    <span class="text-muted small">{{ $comentario->created_at->diffForHumans() }}</span>
                                    @if(!$comentario->leido && !$comentario->es_admin)
                                    <span class="badge bg-danger badge-sm">Nuevo</span>
                                    @endif
                                </div>
                                <p class="mb-0">{{ $comentario->comentario }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-chat-square-dots display-6"></i>
                            <p class="mb-0 mt-2">No hay mensajes aún</p>
                            <small>Escribe el primero para comunicarte con soporte</small>
                        </div>
                        @endforelse
                    </div>

                    {{-- Formulario para nuevo comentario --}}
                    <form action="{{ route('ordenes.comentarios.guardar', $orden->id) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <textarea class="form-control @error('comentario') is-invalid @enderror" 
                                    name="comentario" 
                                    rows="2" 
                                    placeholder="Escribe tu mensaje..."
                                    required></textarea>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-send-fill"></i> Enviar
                            </button>
                        </div>
                        @error('comentario')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            </div>

            @push('scripts')
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Scroll automático al final de los comentarios
                const container = document.querySelector('.comentarios-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }

                // Marcar como leídos al cargar la página
                fetch("{{ route('ordenes.comentarios.marcar-leidos', $orden->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                });

                // Auto-refresh cada 30 segundos (opcional)
                setInterval(() => {
                    // Recargar solo la sección de comentarios
                    location.reload();
                }, 30000);
            });
            </script>
            @endpush

            {{-- Cancelar orden --}}
            @if(in_array($orden->estado, ['pendiente', 'procesando']))
            <div class="card info-card shadow-sm border-danger">
                <div class="card-body text-center">
                    <h6 class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>Zona de peligro
                    </h6>
                    <form action="{{ route('ordenes.cancelar', $orden->id) }}" method="POST" 
                          onsubmit="return confirm('¿Estás seguro de cancelar esta orden?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle"></i> Cancelar orden
                        </button>
                    </form>
                    <small class="text-muted d-block mt-2">El stock será devuelto</small>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal: Dirección --}}
<div class="modal fade" id="direccionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-geo-alt me-2"></i>Dirección de entrega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ordenes.actualizar-info', $orden->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @if($direcciones->count() > 0)
                    <label class="form-label fw-600">Seleccionar dirección guardada:</label>
                    @foreach($direcciones as $dir)
                    <div class="form-check mb-3 p-3 border rounded {{ $orden->direccion_id == $dir->id ? 'border-primary bg-light' : '' }}">
                        <input class="form-check-input" type="radio" name="direccion_id" 
                               id="dir{{ $dir->id }}" value="{{ $dir->id }}"
                               {{ $orden->direccion_id == $dir->id ? 'checked' : '' }}>
                        <label class="form-check-label w-100" for="dir{{ $dir->id }}">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $dir->direccion }}</strong><br>
                                    <small>{{ $dir->ciudad }}, CP: {{ $dir->codigo_postal }}</small><br>
                                    <small><i class="bi bi-telephone"></i> {{ $dir->telefono }}</small>
                                </div>
                                @if($dir->es_principal)
                                <span class="badge bg-primary">Principal</span>
                                @endif
                            </div>
                        </label>
                    </div>
                    @endforeach
                    @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No tienes direcciones guardadas. 
                        <a href="{{ route('home') }}">Agregar desde tu perfil</a>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    @if($direcciones->count() > 0)
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Pago --}}
<div class="modal fade" id="pagoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Completar pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ordenes.completar-pago', $orden->id) }}" method="POST" id="formPago">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    
                    {{-- Monto --}}
                    <div class="alert alert-info mb-4">
                        <h5 class="mb-0">
                            <i class="bi bi-cash me-2"></i>Total a pagar: 
                            <strong class="text-primary">${{ number_format($orden->total, 0, ',', '.') }}</strong>
                        </h5>
                    </div>

                    {{-- Método de pago --}}
                    <label class="form-label fw-600 mb-3">Método de pago:</label>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <input type="radio" class="btn-check" name="metodo_pago" id="metodoPagoTarjeta" value="tarjeta" checked>
                            <label class="btn btn-outline-primary w-100 py-3" for="metodoPagoTarjeta">
                                <i class="bi bi-credit-card fs-3 d-block mb-2"></i>
                                Tarjeta de crédito/débito
                            </label>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" class="btn-check" name="metodo_pago" id="metodoPagoPaypal" value="paypal">
                            <label class="btn btn-outline-primary w-100 py-3" for="metodoPagoPaypal">
                                <i class="bi bi-paypal fs-3 d-block mb-2"></i>
                                PayPal
                            </label>
                        </div>
                    </div>

                    {{-- Formulario Tarjeta --}}
                    <div id="formularioTarjeta" class="pago-form">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-600">Número de tarjeta <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="numero_tarjeta" 
                                       placeholder="1234 5678 9012 3456" maxlength="19" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Nombre del titular <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre_titular" 
                                       placeholder="Como aparece en la tarjeta" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Fecha de expiración <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="fecha_expiracion" 
                                       placeholder="MM/AA" maxlength="5" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">CVV <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="cvv" 
                                       placeholder="123" maxlength="4" required>
                            </div>
                        </div>
                    </div>

                    {{-- Formulario PayPal --}}
                    <div id="formularioPaypal" class="pago-form" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-600">Email de PayPal <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="paypal_email" 
                                   placeholder="tu-email@paypal.com">
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Serás redirigido a PayPal para completar el pago.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-lock-fill me-2"></i>Pagar ${{ number_format($orden->total, 0, ',', '.') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Notas --}}
<div class="modal fade" id="notasModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-chat-left-text me-2"></i>Notas adicionales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ordenes.actualizar-info', $orden->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <label class="form-label fw-600">Instrucciones especiales:</label>
                    <textarea class="form-control" name="notas" rows="4" 
                              placeholder="Ej: Dejar en recepción, tocar timbre 2 veces, etc.">{{ $orden->notas }}</textarea>
                    <small class="text-muted">Máximo 500 caracteres</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle entre formularios de pago
    const radioTarjeta = document.getElementById('metodoPagoTarjeta');
    const radioPaypal = document.getElementById('metodoPagoPaypal');
    const formTarjeta = document.getElementById('formularioTarjeta');
    const formPaypal = document.getElementById('formularioPaypal');
    
    function toggleFormularios() {
        if (radioTarjeta && radioTarjeta.checked) {
            formTarjeta.style.display = 'block';
            formPaypal.style.display = 'none';
            // Hacer campos de tarjeta requeridos
            formTarjeta.querySelectorAll('input').forEach(input => input.required = true);
            formPaypal.querySelectorAll('input').forEach(input => input.required = false);
        } else if (radioPaypal && radioPaypal.checked) {
            formTarjeta.style.display = 'none';
            formPaypal.style.display = 'block';
            // Hacer campo de PayPal requerido
            formTarjeta.querySelectorAll('input').forEach(input => input.required = false);
            formPaypal.querySelectorAll('input').forEach(input => input.required = true);
        }
    }
    
    if (radioTarjeta) radioTarjeta.addEventListener('change', toggleFormularios);
    if (radioPaypal) radioPaypal.addEventListener('change', toggleFormularios);
    
    // Formatear número de tarjeta
    const numeroTarjeta = document.querySelector('input[name="numero_tarjeta"]');
    if (numeroTarjeta) {
        numeroTarjeta.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }
    
    // Formatear fecha de expiración
    const fechaExpiracion = document.querySelector('input[name="fecha_expiracion"]');
    if (fechaExpiracion) {
        fechaExpiracion.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
    }
    
    // Validar solo números en CVV
    const cvv = document.querySelector('input[name="cvv"]');
    if (cvv) {
        cvv.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }
});
</script>
@endpush