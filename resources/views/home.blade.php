@extends('layouts.app')

@section('title', 'Mi Cuenta - Catbox')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-person-circle"></i> Mi Cuenta
                    </h4>
                </div>
                <div class="card-body">
                    <h5>¡Hola, {{ Auth::user()->name }}!</h5>
                    <p class="text-muted">Bienvenido a tu panel de usuario</p>

                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('ordenes.index') }}" class="text-decoration-none">
                                <div class="card text-center h-100 border-primary hover-shadow">
                                    <div class="card-body">
                                        <i class="bi bi-bag-check display-4 text-primary"></i>
                                        <h5 class="mt-2">Mis Órdenes</h5>
                                        <p class="text-muted">Ver historial de compras</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-4 mb-3">
                            <a href="{{ route('carrito.index') }}" class="text-decoration-none">
                                <div class="card text-center h-100 border-success hover-shadow">
                                    <div class="card-body">
                                        <i class="bi bi-cart3 display-4 text-success"></i>
                                        <h5 class="mt-2">Mi Carrito</h5>
                                        <p class="text-muted">Ver carrito de compras</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-4 mb-3">
                            <a href="{{ route('productos.index') }}" class="text-decoration-none">
                                <div class="card text-center h-100 border-danger hover-shadow">
                                    <div class="card-body">
                                        <i class="bi bi-shop display-4 text-danger"></i>
                                        <h5 class="mt-2">Seguir Comprando</h5>
                                        <p class="text-muted">Explorar productos</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    @if(isset($ordenesRecientes) && $ordenesRecientes->count() > 0)
                    <hr>
                    <h5>Órdenes Recientes</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ordenesRecientes as $orden)
                                <tr>
                                    <td><strong>{{ $orden->numero_orden }}</strong></td>
                                    <td>{{ $orden->created_at->format('d/m/Y') }}</td>
                                    <td>${{ number_format($orden->total, 0, ',', '.') }}</td>
                                    <td>
                                        @if($orden->estado === 'pendiente')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @elseif($orden->estado === 'procesando')
                                            <span class="badge bg-info">Procesando</span>
                                        @elseif($orden->estado === 'enviado')
                                            <span class="badge bg-primary">Enviado</span>
                                        @elseif($orden->estado === 'entregado')
                                            <span class="badge bg-success">Entregado</span>
                                        @else
                                            <span class="badge bg-danger">Cancelado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-sm btn-outline-primary">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
</style>
@endpush
@endsection