@extends('layouts.app')

@section('title', 'Mi Carrito - Catbox')

@section('content')
<div class="container my-5">
    <h1 class="mb-4">
        <i class="bi bi-cart3"></i> Mi Carrito
    </h1>

    @if($items->count() > 0)
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->producto->imagenPrincipal)
                                            <img src="{{ asset('storage/' . $item->producto->imagenPrincipal->ruta) }}" 
                                                 alt="{{ $item->producto->nombre }}"
                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                 class="rounded me-3">
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $item->producto->nombre }}</h6>
                                            <small class="text-muted">{{ $item->producto->categoria->nombre }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    ${{ number_format($item->precio_unitario, 0, ',', '.') }}
                                </td>
                                <td class="align-middle">
                                    <form action="{{ route('carrito.actualizar', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <div class="input-group" style="width: 120px;">
                                            <input type="number" 
                                                   name="cantidad" 
                                                   class="form-control form-control-sm" 
                                                   value="{{ $item->cantidad }}" 
                                                   min="1" 
                                                   max="{{ $item->producto->stock }}"
                                                   onchange="this.form.submit()">
                                        </div>
                                    </form>
                                </td>
                                <td class="align-middle">
                                    <strong>${{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                </td>
                                <td class="align-middle">
                                    <form action="{{ route('carrito.eliminar', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('¿Eliminar este producto?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <form action="{{ route('carrito.vaciar') }}" method="POST" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                onclick="return confirm('¿Vaciar todo el carrito?')">
                            <i class="bi bi-trash"></i> Vaciar carrito
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Resumen del pedido --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Resumen del pedido</h5>
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal ({{ $carrito->totalProductos() }} productos)</span>
                        <strong>${{ number_format($total, 0, ',', '.') }}</strong>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Envío</span>
                        <span class="text-success">Gratis</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <h5>Total</h5>
                        <h5 class="text-danger">${{ number_format($total, 0, ',', '.') }}</h5>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('ordenes.checkout') }}" class="btn btn-catbox btn-lg">
                            <i class="bi bi-credit-card"></i> Proceder al pago
                        </a>
                        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Seguir comprando
                        </a>
                    </div>
                </div>
            </div>

            {{-- Información adicional --}}
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6><i class="bi bi-shield-check text-success"></i> Compra segura</h6>
                    <small class="text-muted">Tus datos están protegidos</small>
                    <hr>
                    <h6><i class="bi bi-truck text-primary"></i> Envío rápido</h6>
                    <small class="text-muted">Recibe en 3-5 días hábiles</small>
                    <hr>
                    <h6><i class="bi bi-arrow-clockwise text-info"></i> Devoluciones</h6>
                    <small class="text-muted">30 días para devolver</small>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Carrito vacío --}}
    <div class="text-center py-5">
        <i class="bi bi-cart-x display-1 text-muted"></i>
        <h3 class="mt-3">Tu carrito está vacío</h3>
        <p class="text-muted">¡Agrega productos para comenzar a comprar!</p>
        <a href="{{ route('productos.index') }}" class="btn btn-catbox mt-3">
            <i class="bi bi-shop"></i> Ir a la tienda
        </a>
    </div>
    @endif
</div>
@endsection