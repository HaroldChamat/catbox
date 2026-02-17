@extends('layouts.app')
@section('title', 'Detalle de Orden - Admin')

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
    .nota-usuario-card {
        background: linear-gradient(135deg, #fff5e6 0%, #ffe6cc 100%);
        border-left: 4px solid #ff9800;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
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
<div class="container-fluid">
    <div class="row">

        {{-- Sidebar --}}
        <div class="col-lg-2 d-none d-lg-block admin-sidebar pt-4">
            <p class="text-white-50 small px-3 fw-600 text-uppercase mb-2">Menú Admin</p>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="{{ route('admin.productos.index') }}"><i class="bi bi-box-seam me-2"></i>Productos</a></li>
                <li><a class="nav-link" href="{{ route('admin.categorias.index') }}"><i class="bi bi-tag me-2"></i>Categorías</a></li>
                <li><a class="nav-link active" href="{{ route('admin.ordenes.index') }}"><i class="bi bi-receipt me-2"></i>Órdenes</a></li>
                <li><a class="nav-link" href="{{ route('admin.estadisticas.index') }}"><i class="bi bi-graph-up me-2"></i>Estadísticas</a></li>
            </ul>
        </div>

        {{-- Contenido --}}
        <div class="col-lg-10 py-4 px-4">

            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('admin.ordenes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h3 class="fw-800 mb-0">Orden {{ $orden->numero_orden }}</h3>
                    <small class="text-muted">Creada el {{ $orden->created_at->format('d/m/Y H:i') }}</small>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    {{-- Notas del usuario --}}
                    @if($orden->notas)
                    <div class="nota-usuario-card">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-chat-left-quote-fill text-warning fs-4"></i>
                            <div class="flex-grow-1">
                                <h6 class="fw-700 mb-2 text-dark">
                                    <i class="bi bi-person-circle me-1"></i>
                                    Notas del Cliente
                                </h6>
                                <p class="mb-0 fst-italic text-dark">"{{ $orden->notas }}"</p>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-clock me-1"></i>
                                    Agregada el {{ $orden->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Productos --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0">Productos</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orden->detalles as $detalle)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ producto_imagen($detalle->producto) }}"
                                                     style="width:50px;height:50px;object-fit:cover;border-radius:8px"
                                                     onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                                <div>
                                                    <div class="fw-600">{{ $detalle->producto->nombre }}</div>
                                                    <small class="text-muted">{{ $detalle->producto->categoria->nombre }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td class="fw-700">${{ number_format($detalle->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    @php
                                        $datosPago = $orden->pago?->datos_pago ?? [];
                                        $descuento = $datosPago['descuento'] ?? 0;
                                        $totalOriginal = $datosPago['total_original'] ?? $orden->total;
                                        $cuponCodigo = $datosPago['cupon'] ?? null;
                                        
                                        // Calcular porcentaje de descuento
                                        $porcentajeDescuento = $totalOriginal > 0 
                                            ? round(($descuento / $totalOriginal) * 100, 1) 
                                            : 0;
                                    @endphp

                                    @if($descuento > 0)
                                    <tr>
                                        <td colspan="3" class="text-end">Subtotal:</td>
                                        <td class="fw-600">${{ number_format($totalOriginal, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td colspan="3" class="text-end text-success">
                                            <i class="bi bi-ticket-perforated-fill me-1"></i>
                                            Descuento aplicado: {{ $porcentajeDescuento }}%
                                            @if($cuponCodigo)
                                            <br>
                                            <small class="ms-1">(Cupón: <code class="text-success bg-white px-2 py-1 rounded">{{ $cuponCodigo }}</code>)</small>
                                            @endif
                                        </td>
                                        <td class="fw-600 text-success">- ${{ number_format($descuento, 0, ',', '.') }}</td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td colspan="3" class="text-end fw-700">TOTAL PAGADO:</td>
                                        <td class="fw-800 text-danger fs-5">${{ number_format($orden->total, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    {{-- Info del cliente --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0"><i class="bi bi-person me-2"></i>Cliente</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">Nombre</small>
                                <div class="fw-600">{{ $orden->user->name }}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Email</small>
                                <div>{{ $orden->user->email }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Dirección de entrega --}}
                    @if($orden->direccion)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0"><i class="bi bi-geo-alt me-2"></i>Dirección de entrega</h6>
                        </div>
                        <div class="card-body">
                            <div class="p-3 bg-light rounded">
                                <p class="mb-2"><i class="bi bi-house-door text-primary me-2"></i><strong>{{ $orden->direccion->direccion }}</strong></p>
                                <p class="mb-2"><i class="bi bi-building text-primary me-2"></i>{{ $orden->direccion->ciudad }}</p>
                                <p class="mb-2"><i class="bi bi-mailbox text-primary me-2"></i>CP: {{ $orden->direccion->codigo_postal }}</p>
                                <p class="mb-0"><i class="bi bi-telephone text-primary me-2"></i>{{ $orden->direccion->telefono }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Estado --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0"><i class="bi bi-truck me-2"></i>Estado</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.ordenes.updateestado', $orden->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="estado" class="form-select mb-3">
                                    <option value="pendiente" {{ $orden->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="procesando" {{ $orden->estado == 'procesando' ? 'selected' : '' }}>Procesando</option>
                                    <option value="enviado" {{ $orden->estado == 'enviado' ? 'selected' : '' }}>Enviado</option>
                                    <option value="entregado" {{ $orden->estado == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                    <option value="cancelado" {{ $orden->estado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                                <button type="submit" class="btn btn-primary w-100">Actualizar Estado</button>
                            </form>
                        </div>
                    </div>

                    {{-- Info de pago --}}
                    @if($orden->pago)
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="fw-700 mb-0"><i class="bi bi-credit-card me-2"></i>Pago</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block">Método</small>
                                <div class="text-capitalize">{{ $orden->pago->metodo_pago }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">Estado</small>
                                <span class="badge bg-{{ $orden->pago->estado == 'completado' ? 'success' : 'warning' }}">
                                    {{ ucfirst($orden->pago->estado) }}
                                </span>
                            </div>

                            @php
                                $datosPago = $orden->pago->datos_pago ?? [];
                                $cupon = $datosPago['cupon'] ?? null;
                                $descuento = $datosPago['descuento'] ?? 0;
                            @endphp

                            @if($cupon)
                            <div class="mb-2">
                                <small class="text-muted d-block">Cupón aplicado</small>
                                <div>
                                    <code class="bg-success text-white px-2 py-1 rounded">{{ $cupon }}</code>
                                    <small class="text-success ms-1">
                                        (- ${{ number_format($descuento, 0, ',', '.') }})
                                    </small>
                                </div>
                            </div>
                            @endif

                            <div>
                                <small class="text-muted d-block">ID Transacción</small>
                                <code class="small">{{ $orden->pago->transaction_id }}</code>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            {{-- Comentarios / Chat con el cliente --}}
            <div class="col-lg-12 mt-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-700 mb-0">
                            <i class="bi bi-chat-dots text-danger me-2"></i>
                            Mensajes con el cliente
                            @if($orden->comentariosNoLeidosPara(true) > 0)
                            <span class="badge bg-danger">{{ $orden->comentariosNoLeidosPara(true) }} nuevos</span>
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
                                        <strong class="small">Admin - {{ $comentario->user->name }}</strong>
                                        @else
                                        <i class="bi bi-person-circle text-secondary"></i>
                                        <strong class="small">Cliente - {{ $comentario->user->name }}</strong>
                                        @endif
                                        <span class="text-muted small">{{ $comentario->created_at->diffForHumans() }}</span>
                                        @if(!$comentario->leido && $comentario->es_admin == false)
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
                            </div>
                            @endforelse
                        </div>

                        {{-- Formulario para nuevo comentario --}}
                        <form action="{{ route('admin.ordenes.comentarios.guardar', $orden->id) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <textarea class="form-control @error('comentario') is-invalid @enderror" 
                                        name="comentario" 
                                        rows="2" 
                                        placeholder="Escribe una respuesta al cliente..."
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
            </div>

            @push('scripts')
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Scroll automático al final
                const container = document.querySelector('.comentarios-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }

                // Marcar como leídos
                fetch("{{ route('admin.ordenes.comentarios.marcar-leidos', $orden->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                });
            });
            </script>
            @endpush
        </div>
    </div>
</div>
@endsection