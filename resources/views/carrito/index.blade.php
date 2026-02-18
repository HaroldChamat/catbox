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
                                        <img src="{{ producto_imagen($item->producto) }}" 
                                             alt="{{ $item->producto->nombre }}"
                                             style="width: 60px; height: 60px; object-fit: cover;"
                                             class="rounded me-3"
                                             onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
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
                    
                    {{-- Cupón de descuento --}}
                    @if(session('cupon'))
                    @php
                        $cuponSesion = session('cupon');
                        $descuento = 0;
                        $cuponObj = \App\Models\Cupon::with('productos')->find($cuponSesion['id']);
                        if ($cuponObj) {
                            foreach ($items as $item) {
                                if ($cuponObj->aplicaA($item->producto)) {
                                    $descuento += $cuponObj->calcularDescuento($item->subtotal);
                                }
                            }
                        }
                        $totalConDescuento = max(0, $total - $descuento);
                    @endphp

                    {{-- Créditos disponibles --}}
                    @if($saldoCreditosTotal > 0)
                    <div class="alert alert-success py-2 px-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-wallet2"></i>
                                <strong>Crédito disponible:</strong> 
                                ${{ number_format($saldoCreditosTotal, 0, ',', '.') }}
                            </div>
                            @if(session('usar_credito'))
                            <form action="{{ route('credito.quitar') }}" method="POST" class="m-0">
                                @csrf
                                <button class="btn btn-sm btn-link text-danger p-0" title="No usar crédito">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @else
                            <form action="{{ route('credito.aplicar') }}" method="POST" class="m-0">
                                @csrf
                                <button class="btn btn-sm btn-success">Usar crédito</button>
                            </form>
                            @endif
                        </div>
                    </div>

                    @if(session('usar_credito'))
                    @php
                        $creditoAplicado = min($saldoCreditosTotal, session('cupon') ? $totalConDescuento : $total);
                        $totalFinalConCredito = max(0, (session('cupon') ? $totalConDescuento : $total) - $creditoAplicado);
                    @endphp
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Crédito aplicado</span>
                        <strong>- ${{ number_format($creditoAplicado, 0, ',', '.') }}</strong>
                    </div>
                    @endif
                    @endif

                    <hr>

                    <div class="alert alert-success py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-ticket-perforated-fill"></i>
                            <strong>{{ $cuponSesion['codigo'] }}</strong>
                            <small class="ms-1 text-muted">
                                ({{ $cuponSesion['tipo'] === 'porcentaje' ? $cuponSesion['valor'].'%' : '$'.number_format($cuponSesion['valor'],0,',','.') }} desc.)
                            </small>
                        </div>
                        <form action="{{ route('cupon.quitar') }}" method="POST" class="m-0">
                            @csrf
                            <button class="btn btn-sm btn-link text-danger p-0" title="Quitar cupón">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Descuento aplicado</span>
                        <strong>- ${{ number_format($descuento, 0, ',', '.') }}</strong>
                    </div>
                    @else
                    <form action="{{ route('cupon.aplicar') }}" method="POST" class="mb-3">
                        @csrf
                        <label class="form-label small fw-bold">¿Tienes un cupón?</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="codigo" class="form-control text-uppercase"
                                   placeholder="Ej: CAT-ABC123"
                                   value="{{ old('codigo') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-check-lg"></i> Aplicar
                            </button>
                        </div>
                        @error('cupon')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </form>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <h5>Total</h5>
                        <h5 class="text-danger">
                            @php
                                $totalFinal = $total;
                                
                                // Aplicar descuento de cupón si existe
                                if(session('cupon') && isset($totalConDescuento)) {
                                    $totalFinal = $totalConDescuento;
                                }
                                
                                // Aplicar crédito si está activado
                                if(session('usar_credito') && $saldoCreditosTotal > 0) {
                                    $creditoAUsar = min($saldoCreditosTotal, $totalFinal);
                                    $totalFinal = max(0, $totalFinal - $creditoAUsar);
                                }
                            @endphp

                            @if(session('usar_credito') || (session('cupon') && isset($totalConDescuento)))
                                <span class="text-muted text-decoration-line-through fs-6 me-1">
                                    ${{ number_format($total, 0, ',', '.') }}
                                </span>
                            @endif
                            ${{ number_format($totalFinal, 0, ',', '.') }}
                        </h5>
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