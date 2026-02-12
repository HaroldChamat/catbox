@extends('layouts.app')

@section('content')
<div class="container-fluid py-5" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh;">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="{{ route('ordenes.index') }}" class="btn btn-outline-light mb-3">
                    <i class="bi bi-arrow-left me-2"></i>Volver a mis órdenes
                </a>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="text-white fw-bold mb-2">
                            <i class="bi bi-receipt me-2"></i>Orden #{{ $orden->id }}
                        </h1>
                        <p class="text-white-50 mb-0">
                            Realizada el {{ $orden->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div>
                        @php
                            $badgeClass = match($orden->estado) {
                                'pendiente' => 'bg-warning text-dark',
                                'procesando' => 'bg-info',
                                'enviado' => 'bg-primary',
                                'entregado' => 'bg-success',
                                'cancelado' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            $iconClass = match($orden->estado) {
                                'pendiente' => 'bi-clock',
                                'procesando' => 'bi-arrow-repeat',
                                'enviado' => 'bi-truck',
                                'entregado' => 'bi-check-circle',
                                'cancelado' => 'bi-x-circle',
                                default => 'bi-question-circle'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} px-4 py-2 fs-5">
                            <i class="{{ $iconClass }} me-2"></i>{{ ucfirst($orden->estado) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensajes de éxito/error -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Columna izquierda: Productos y timeline -->
            <div class="col-lg-8">
                <!-- Timeline de estado -->
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Estado de tu orden</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline-container py-3">
                            @php
                                $estados = [
                                    'pendiente' => ['icon' => 'bi-clock', 'label' => 'Pendiente', 'color' => 'warning'],
                                    'procesando' => ['icon' => 'bi-arrow-repeat', 'label' => 'Procesando', 'color' => 'info'],
                                    'enviado' => ['icon' => 'bi-truck', 'label' => 'Enviado', 'color' => 'primary'],
                                    'entregado' => ['icon' => 'bi-check-circle', 'label' => 'Entregado', 'color' => 'success'],
                                ];
                                
                                $estadoActualIndex = array_search($orden->estado, array_keys($estados));
                            @endphp

                            <div class="d-flex justify-content-between position-relative">
                                <!-- Línea de progreso -->
                                <div class="position-absolute top-50 start-0 translate-middle-y" style="width: 100%; height: 3px; background: #e0e0e0; z-index: 0;"></div>
                                @if($orden->estado !== 'cancelado' && $estadoActualIndex !== false)
                                    <div class="position-absolute top-50 start-0 translate-middle-y bg-success" 
                                         style="width: {{ ($estadoActualIndex / (count($estados) - 1)) * 100 }}%; height: 3px; z-index: 1; transition: width 0.5s;"></div>
                                @endif

                                @foreach($estados as $key => $estado)
                                    @php
                                        $index = array_search($key, array_keys($estados));
                                        $isActive = $index <= $estadoActualIndex && $orden->estado !== 'cancelado';
                                        $isCurrent = $key === $orden->estado;
                                    @endphp
                                    <div class="text-center position-relative" style="z-index: 2; flex: 1;">
                                        <div class="mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center {{ $isActive ? 'bg-' . $estado['color'] : 'bg-light' }} {{ $isCurrent ? 'shadow-lg' : '' }}" 
                                             style="width: 50px; height: 50px; {{ $isCurrent ? 'transform: scale(1.2);' : '' }}">
                                            <i class="bi {{ $estado['icon'] }} fs-4 {{ $isActive ? 'text-white' : 'text-muted' }}"></i>
                                        </div>
                                        <small class="fw-bold {{ $isActive ? 'text-' . $estado['color'] : 'text-muted' }}">
                                            {{ $estado['label'] }}
                                        </small>
                                    </div>
                                @endforeach

                                @if($orden->estado === 'cancelado')
                                    <div class="text-center position-relative" style="z-index: 2; flex: 1;">
                                        <div class="mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center bg-danger shadow-lg" 
                                             style="width: 50px; height: 50px; transform: scale(1.2);">
                                            <i class="bi bi-x-circle fs-4 text-white"></i>
                                        </div>
                                        <small class="fw-bold text-danger">Cancelado</small>
                                    </div>
                                @endif
                            </div>

                            @if($orden->fecha_entrega_estimada && $orden->estado !== 'cancelado' && $orden->estado !== 'entregado')
                                <div class="alert alert-info mt-4 mb-0">
                                    <i class="bi bi-calendar-event me-2"></i>
                                    <strong>Fecha estimada de entrega:</strong> {{ \Carbon\Carbon::parse($orden->fecha_entrega_estimada)->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Productos de la orden -->
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-bag-check me-2"></i>Productos ({{ $orden->detalles->count() }})</h5>
                        <h4 class="mb-0 text-warning fw-bold">${{ number_format($orden->total, 2) }}</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orden->detalles as $detalle)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($detalle->producto->imagenPrincipal)
                                                        <img src="{{ asset('storage/' . $detalle->producto->imagenPrincipal->ruta) }}" 
                                                             alt="{{ $detalle->producto->nombre }}"
                                                             class="rounded me-3"
                                                             style="width: 80px; height: 80px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                             style="width: 80px; height: 80px;">
                                                            <i class="bi bi-image text-muted fs-3"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-1 fw-bold">{{ $detalle->producto->nombre }}</h6>
                                                        <small class="text-muted">
                                                            <i class="bi bi-tag me-1"></i>{{ $detalle->producto->categoria->nombre }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge bg-secondary fs-6">{{ $detalle->cantidad }}</span>
                                            </td>
                                            <td class="text-end align-middle">
                                                <strong>${{ number_format($detalle->precio_unitario, 2) }}</strong>
                                            </td>
                                            <td class="text-end align-middle">
                                                <strong class="text-primary fs-5">${{ number_format($detalle->subtotal, 2) }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                        <td class="text-end">
                                            <h4 class="mb-0 text-danger fw-bold">${{ number_format($orden->total, 2) }}</h4>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Info de entrega y pago -->
            <div class="col-lg-4">
                <!-- Dirección de entrega -->
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Dirección de entrega</h5>
                        @if(in_array($orden->estado, ['pendiente', 'procesando']))
                            <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#editarDireccionModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($orden->direccion_id && $orden->direccion)
                            <div class="p-3 bg-light rounded">
                                <p class="mb-2"><i class="bi bi-house-door me-2 text-primary"></i><strong>{{ $orden->direccion->direccion }}</strong></p>
                                @if($orden->direccion->ciudad)
                                    <p class="mb-2"><i class="bi bi-building me-2 text-primary"></i>{{ $orden->direccion->ciudad }}</p>
                                @endif
                                @if($orden->direccion->codigo_postal)
                                    <p class="mb-2"><i class="bi bi-mailbox me-2 text-primary"></i>CP: {{ $orden->direccion->codigo_postal }}</p>
                                @endif
                                @if($orden->direccion->telefono)
                                    <p class="mb-0"><i class="bi bi-telephone me-2 text-primary"></i>{{ $orden->direccion->telefono }}</p>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>¡Atención!</strong> No has agregado una dirección de entrega.
                                @if(in_array($orden->estado, ['pendiente', 'procesando']))
                                    <button class="btn btn-sm btn-warning mt-2 w-100" data-bs-toggle="modal" data-bs-target="#editarDireccionModal">
                                        <i class="bi bi-plus-circle me-2"></i>Agregar dirección ahora
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Información de pago -->
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Información de pago</h5>
                        @if($orden->estado === 'pendiente' && (!$orden->pago || $orden->pago->estado !== 'completado'))
                            <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#completarPagoModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($orden->pago && $orden->pago->estado === 'completado')
                            <div class="p-3 bg-light rounded">
                                <p class="mb-2">
                                    <i class="bi bi-{{ $orden->pago->metodo_pago === 'tarjeta' ? 'credit-card' : 'paypal' }} me-2 text-success"></i>
                                    <strong>{{ ucfirst($orden->pago->metodo_pago) }}</strong>
                                </p>
                                @if($orden->pago->datos_pago && isset($orden->pago->datos_pago['ultimos_digitos']))
                                    <p class="mb-2">
                                        <i class="bi bi-shield-check me-2 text-success"></i>
                                        **** **** **** {{ $orden->pago->datos_pago['ultimos_digitos'] }}
                                    </p>
                                @endif
                                @if($orden->pago->datos_pago && isset($orden->pago->datos_pago['paypal_email']))
                                    <p class="mb-2">
                                        <i class="bi bi-envelope me-2 text-success"></i>
                                        {{ $orden->pago->datos_pago['paypal_email'] }}
                                    </p>
                                @endif
                                <p class="mb-2">
                                    <i class="bi bi-cash me-2 text-success"></i>
                                    Monto: <strong class="text-success">${{ number_format($orden->pago->monto, 2) }}</strong>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-check-circle me-2 text-success"></i>
                                    Estado: <span class="badge bg-success">{{ ucfirst($orden->pago->estado) }}</span>
                                </p>
                                @if($orden->pago->transaction_id)
                                    <hr>
                                    <small class="text-muted">
                                        <i class="bi bi-hash me-1"></i>ID de transacción: {{ $orden->pago->transaction_id }}
                                    </small>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>¡Atención!</strong> El pago de esta orden está pendiente.
                                @if($orden->estado === 'pendiente')
                                    <button class="btn btn-sm btn-warning mt-2 w-100" data-bs-toggle="modal" data-bs-target="#completarPagoModal">
                                        <i class="bi bi-credit-card me-2"></i>Completar pago ahora
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Notas adicionales -->
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Notas</h5>
                        @if(in_array($orden->estado, ['pendiente', 'procesando']))
                            <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#editarNotasModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($orden->notas)
                            <p class="mb-0 fst-italic">{{ $orden->notas }}</p>
                        @else
                            <p class="text-muted mb-0">Sin notas adicionales</p>
                            @if(in_array($orden->estado, ['pendiente', 'procesando']))
                                <button class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#editarNotasModal">
                                    <i class="bi bi-plus-circle me-2"></i>Agregar notas
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Acciones -->
                @if(in_array($orden->estado, ['pendiente', 'procesando']))
                    <div class="card shadow-lg border-0 border-danger">
                        <div class="card-body text-center">
                            <h6 class="text-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Zona de peligro</h6>
                            <form action="{{ route('ordenes.cancelar', $orden->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta orden? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar orden
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">Al cancelar, el stock será devuelto</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Dirección -->
<div class="modal fade" id="editarDireccionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-geo-alt me-2"></i>Dirección de entrega</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ordenes.actualizar-info', $orden->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @if($direcciones->count() > 0)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Seleccionar dirección guardada:</label>
                            @foreach($direcciones as $dir)
                                <div class="form-check mb-2 p-3 border rounded {{ $orden->direccion_id == $dir->id ? 'border-primary bg-light' : '' }}">
                                    <input class="form-check-input" type="radio" name="direccion_id" 
                                           id="dir{{ $dir->id }}" value="{{ $dir->id }}"
                                           {{ $orden->direccion_id == $dir->id ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="dir{{ $dir->id }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>{{ $dir->direccion }}</strong>
                                                @if($dir->ciudad)<br><small>{{ $dir->ciudad }}</small>@endif
                                                @if($dir->codigo_postal)<br><small>CP: {{ $dir->codigo_postal }}</small>@endif
                                                @if($dir->telefono)<br><small><i class="bi bi-telephone me-1"></i>{{ $dir->telefono }}</small>@endif
                                            </div>
                                            @if($dir->es_principal)
                                                <span class="badge bg-primary">Principal</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>No tienes direcciones guardadas. 
                            <a href="{{ route('home') }}" class="alert-link">Agregar una desde tu perfil</a>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    @if($direcciones->count() > 0)
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Guardar cambios
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Completar Pago -->
<div class="modal fade" id="completarPagoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Completar pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ordenes.completar-pago', $orden->id) }}" method="POST" id="formPago">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Monto a pagar -->
                    <div class="alert alert-info">
                        <h5 class="mb-0">
                            <i class="bi bi-cash me-2"></i>Total a pagar: 
                            <strong class="text-primary">${{ number_format($orden->total, 2) }}</strong>
                        </h5>
                    </div>

                    <!-- Método de pago -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Método de pago:</label>
                        <div class="row g-3">
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
                    </div>

                    <!-- Formulario Tarjeta -->
                    <div id="formularioTarjeta">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Número de tarjeta</label>
                                <input type="text" class="form-control" name="numero_tarjeta" 
                                       placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nombre del titular</label>
                                <input type="text" class="form-control" name="nombre_titular" 
                                       placeholder="Como aparece en la tarjeta">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de expiración</label>
                                <input type="text" class="form-control" name="fecha_expiracion" 
                                       placeholder="MM/AA" maxlength="5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" name="cvv" 
                                       placeholder="123" maxlength="4">
                            </div>
                        </div>
                    </div>

                    <!-- Formulario PayPal -->
                    <div id="formularioPaypal" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Email de PayPal</label>
                            <input type="email" class="form-control" name="paypal_email" 
                                   placeholder="tu-email@paypal.com">
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Serás redirigido a PayPal para completar el pago de forma segura.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-lock-fill me-2"></i>Pagar ${{ number_format($orden->total, 2) }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Notas -->
<div class="modal fade" id="editarNotasModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-chat-left-text me-2"></i>Notas adicionales</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ordenes.actualizar-info', $orden->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Instrucciones especiales de entrega:</label>
                        <textarea class="form-control" name="notas" rows="4" 
                                  placeholder="Ej: Dejar en recepción, tocar timbre 2 veces, etc.">{{ $orden->notas }}</textarea>
                        <small class="text-muted">Máximo 500 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Guardar notas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.timeline-container {
    position: relative;
}

@media (max-width: 768px) {
    .timeline-container .d-flex {
        flex-direction: column !important;
    }
    
    .timeline-container .d-flex > div {
        margin-bottom: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle entre formularios de pago
    const radioTarjeta = document.getElementById('metodoPagoTarjeta');
    const radioPaypal = document.getElementById('metodoPagoPaypal');
    const formTarjeta = document.getElementById('formularioTarjeta');
    const formPaypal = document.getElementById('formularioPaypal');
    
    function toggleFormularios() {
        if (radioTarjeta.checked) {
            formTarjeta.style.display = 'block';
            formPaypal.style.display = 'none';
            // Hacer campos de tarjeta requeridos
            formTarjeta.querySelectorAll('input').forEach(input => input.required = true);
            formPaypal.querySelectorAll('input').forEach(input => input.required = false);
        } else {
            formTarjeta.style.display = 'none';
            formPaypal.style.display = 'block';
            // Hacer campo de PayPal requerido
            formTarjeta.querySelectorAll('input').forEach(input => input.required = false);
            formPaypal.querySelectorAll('input').forEach(input => input.required = true);
        }
    }
    
    radioTarjeta.addEventListener('change', toggleFormularios);
    radioPaypal.addEventListener('change', toggleFormularios);
    
    // Formatear número de tarjeta
    const numeroTarjeta = document.querySelector('input[name="numero_tarjeta"]');
    if (numeroTarjeta) {
        numeroTarjeta.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
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
});
</script>
@endsection