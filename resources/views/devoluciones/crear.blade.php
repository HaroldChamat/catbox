@extends('layouts.app')
@section('title', 'Solicitar Devolución')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-arrow-return-left me-2"></i>Solicitar Devolución</h5>
                    <small class="text-muted">Orden: {{ $orden->numero_orden }}</small>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Política de devoluciones:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Tienes 30 días desde la entrega para solicitar una devolución</li>
                            <li>Puedes seleccionar productos específicos a devolver</li>
                            <li>El monto se generará como crédito en tu cuenta</li>
                            <li>Tu solicitud será revisada por nuestro equipo</li>
                        </ul>
                    </div>

                    <form action="{{ route('devoluciones.guardar', $orden->id) }}" method="POST" id="form-devolucion">
                        @csrf

                        <h6 class="mb-3">Selecciona los productos a devolver:</h6>

                        @foreach($orden->detalles as $detalle)
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        <div class="form-check">
                                            <input class="form-check-input item-checkbox" 
                                                   type="checkbox" 
                                                   value="1"
                                                   id="item{{ $detalle->id }}"
                                                   data-detalle-id="{{ $detalle->id }}"
                                                   data-precio="{{ $detalle->precio_unitario }}"
                                                   data-max="{{ $detalle->cantidad }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <img src="{{ producto_imagen($detalle->producto) }}"
                                             class="img-fluid rounded"
                                             style="height: 60px; object-fit: cover;"
                                             onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                    </div>
                                    <div class="col-md-5">
                                        <h6 class="mb-1">{{ $detalle->producto->nombre }}</h6>
                                        <small class="text-muted">
                                            Precio: ${{ number_format($detalle->precio_unitario, 0, ',', '.') }} × {{ $detalle->cantidad }}
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Cantidad a devolver:</label>
                                        <input type="number" 
                                               class="form-control form-control-sm cantidad-input"
                                               id="cantidad{{ $detalle->id }}"
                                               data-detalle-id="{{ $detalle->id }}"
                                               min="1" 
                                               max="{{ $detalle->cantidad }}"
                                               value="{{ $detalle->cantidad }}"
                                               disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="mb-3 mt-4">
                            <label class="form-label">Motivo de la devolución <span class="text-danger">*</span></label>
                            <textarea name="motivo" class="form-control" rows="4" 
                                      placeholder="Explica por qué deseas devolver estos productos (mínimo 20 caracteres)..." 
                                      required>{{ old('motivo') }}</textarea>
                            <small class="text-muted">Mínimo 20 caracteres</small>
                        </div>

                        <div class="alert alert-warning">
                            <strong>Monto a reembolsar:</strong> 
                            <span id="monto-total" class="fs-5">$0</span>
                            <small class="d-block mt-1">Este monto se generará como crédito en tu cuenta</small>
                        </div>

                        <div id="items-container"></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger" id="btn-submit" disabled>
                                <i class="bi bi-send"></i> Enviar solicitud
                            </button>
                            <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function actualizarMonto() {
    let total = 0;
    let itemsSeleccionados = 0;
    const container = document.getElementById('items-container');
    container.innerHTML = '';

    document.querySelectorAll('.item-checkbox:checked').forEach((checkbox, index) => {
        const detalleId = checkbox.dataset.detalleId;
        const precio = parseFloat(checkbox.dataset.precio);
        const cantidadInput = document.getElementById('cantidad' + detalleId);
        const cantidad = parseInt(cantidadInput.value) || 0;
        
        if (cantidad > 0) {
            total += precio * cantidad;
            itemsSeleccionados++;

            // Crear campos hidden
            const inputDetalleId = document.createElement('input');
            inputDetalleId.type = 'hidden';
            inputDetalleId.name = `items[${index}][detalle_id]`;
            inputDetalleId.value = detalleId;
            container.appendChild(inputDetalleId);
            
            const inputCantidad = document.createElement('input');
            inputCantidad.type = 'hidden';
            inputCantidad.name = `items[${index}][cantidad]`;
            inputCantidad.value = cantidad;
            container.appendChild(inputCantidad);
        }
    });

    document.getElementById('monto-total').textContent = 
        '$' + Math.round(total).toLocaleString('es-CO');

    document.getElementById('btn-submit').disabled = itemsSeleccionados === 0;
}

document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const detalleId = this.dataset.detalleId;
        const cantidadInput = document.getElementById('cantidad' + detalleId);
        cantidadInput.disabled = !this.checked;
        
        if (!this.checked) {
            const max = parseInt(this.dataset.max);
            cantidadInput.value = max;
        }
        
        actualizarMonto();
    });
});

document.querySelectorAll('.cantidad-input').forEach(input => {
    input.addEventListener('input', actualizarMonto);
});

// Validar antes de enviar
document.getElementById('form-devolucion').addEventListener('submit', function(e) {
    const itemsSeleccionados = document.querySelectorAll('.item-checkbox:checked').length;
    
    if (itemsSeleccionados === 0) {
        e.preventDefault();
        alert('Debes seleccionar al menos un producto');
        return false;
    }
});

actualizarMonto();
</script>
@endpush
@endsection