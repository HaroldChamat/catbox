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
                                    <tr>
                                        <td colspan="3" class="text-end fw-700">TOTAL:</td>
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
                            <form action="{{ route('admin.ordenes.cambiar-estado', $orden->id) }}" method="POST">
                                @csrf
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
                            <div>
                                <small class="text-muted d-block">ID Transacción</small>
                                <code class="small">{{ $orden->pago->transaction_id }}</code>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection