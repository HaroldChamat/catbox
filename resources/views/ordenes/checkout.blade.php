@extends('layouts.app')

@section('title', 'Finalizar Compra - Catbox')

@section('content')
<div class="container my-5">
    <h1 class="mb-4">
        <i class="bi bi-credit-card"></i> Finalizar Compra
    </h1>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('ordenes.procesar') }}" method="POST">
                @csrf

                   {{-- Método de pago --}}
                    @if($totalFinal > 0)
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-wallet2"></i> Método de Pago
                            </h5>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="metodo_pago" id="tarjeta" value="tarjeta" checked>
                                <label class="form-check-label" for="tarjeta">
                                    <i class="bi bi-credit-card"></i> Tarjeta de Crédito/Débito
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodo_pago" id="paypal" value="paypal">
                                <label class="form-check-label" for="paypal">
                                    <i class="bi bi-paypal text-primary"></i> PayPal
                                </label>
                            </div>
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="metodo_pago" value="credito">
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Pago cubierto con crédito</strong>
                        <p class="mb-0 mt-1 small">No necesitas ingresar datos de pago. Confirma tu orden directamente.</p>
                    </div>
                    @endif

                    {{-- Usar crédito --}}
                    @if($saldoCreditosTotal > 0 && session('usar_credito'))
                    <div class="card shadow-sm mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-wallet2 me-2"></i>
                                Crédito Aplicado
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <strong>Crédito disponible:</strong> ${{ number_format($saldoCreditosTotal, 0, ',', '.') }}
                                </div>
                                <div>
                                    <strong class="text-success">Aplicado: -${{ number_format($creditoAplicado, 0, ',', '.') }}</strong>
                                </div>
                            </div>

                            @if($totalFinal == 0)
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>¡Tu crédito cubre el total!</strong>
                                <p class="mb-0 mt-1 small">
                                    No necesitas ingresar datos de pago. Al confirmar, tu pedido se procesará automáticamente.
                                </p>
                            </div>
                            @else
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Pago parcial con crédito</strong>
                                <p class="mb-0 mt-1">
                                    Tu crédito cubre ${{ number_format($creditoAplicado, 0, ',', '.') }}<br>
                                    Solo pagarás la diferencia: <strong>${{ number_format($totalFinal, 0, ',', '.') }}</strong>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @elseif($saldoCreditosTotal > 0 && !session('usar_credito'))
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Tienes ${{ number_format($saldoCreditosTotal, 0, ',', '.') }} de crédito disponible</strong>
                        <p class="mb-2 mt-1 small">Puedes usarlo para pagar total o parcialmente.</p>
                        <a href="{{ route('carrito.index') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-arrow-left"></i> Volver al carrito para aplicar crédito
                        </a>
                    </div>
                    @endif




                {{-- Dirección de envío --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-geo-alt"></i> Dirección de Envío
                        </h5>

                        @if($direcciones->count() > 0)
                            @foreach($direcciones as $direccion)
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="direccion_id" 
                                       id="dir{{ $direccion->id }}" 
                                       value="{{ $direccion->id }}"
                                       {{ $direccion->es_principal ? 'checked' : '' }}>
                                <label class="form-check-label" for="dir{{ $direccion->id }}">
                                    {{ $direccion->direccion }}, {{ $direccion->ciudad }} - {{ $direccion->codigo_postal }}
                                    @if($direccion->es_principal)
                                        <span class="badge bg-primary">Principal</span>
                                    @endif
                                </label>
                            </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> 
                                No tienes direcciones registradas. Por favor, agrega una dirección de envío.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Botón de confirmar --}}
                <div class="d-grid gap-2">
                    @if($totalFinal == 0)
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Confirmar pedido (Pago con crédito)
                        </button>
                        <small class="text-center text-muted">
                            Tu crédito cubre el total. La orden se procesará automáticamente.
                        </small>
                    @else
                        <button type="submit" class="btn btn-catbox btn-lg">
                            <i class="bi bi-check-circle"></i> Confirmar y continuar al pago
                        </button>
                    @endif
                    <a href="{{ route('carrito.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al carrito
                    </a>
                </div>
            </form>
        </div>

        {{-- Resumen de la orden --}}
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 100px;">
                <div class="card-body">
                    <h5 class="card-title">Resumen de la Orden</h5>
                    <hr>

                    {{-- Productos --}}
                    @foreach($items as $item)
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <small class="d-block">{{ Str::limit($item->producto->nombre, 30) }}</small>
                            <small class="text-muted">Cant: {{ $item->cantidad }}</small>
                        </div>
                        <small><strong>${{ number_format($item->subtotal, 0, ',', '.') }}</strong></small>
                    </div>
                    @endforeach

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <strong>${{ number_format($total, 0, ',', '.') }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span>Envío</span>
                        <span class="text-success">Gratis</span>
                    </div>

                    @if($descuento > 0 && $cuponAplicado)
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>
                            <i class="bi bi-ticket-perforated-fill me-1"></i>
                            Cupón <code>{{ $cuponAplicado->codigo }}</code>
                        </span>
                        <strong>- ${{ number_format($descuento, 0, ',', '.') }}</strong>
                    </div>
                    @endif

                    @if($creditoAplicado > 0)
                    <div class="d-flex justify-content-between mb-2 text-primary">
                        <span>
                            <i class="bi bi-wallet2 me-1"></i>
                            Crédito aplicado
                        </span>
                        <strong>- ${{ number_format($creditoAplicado, 0, ',', '.') }}</strong>
                    </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between">
                        <h5>Total a pagar</h5>
                        <h5 class="text-danger">
                            @if($descuento > 0 || $creditoAplicado > 0)
                                <span class="text-muted text-decoration-line-through fs-6 me-1">
                                    ${{ number_format($total, 0, ',', '.') }}
                                </span>
                            @endif
                            @if($totalFinal == 0)
                                <span class="text-success">$0 (Pagado con crédito)</span>
                            @else
                                ${{ number_format($totalFinal, 0, ',', '.') }}
                            @endif
                        </h5>
                    </div>

                    @if($totalFinal == 0)
                    <div class="alert alert-success mt-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>¡Pedido cubierto completamente!</strong>
                        <small class="d-block mt-1">El crédito cubre el costo total</small>
                    </div>
                    @endif

            {{-- Información de seguridad --}}
            <div class="card shadow-sm mt-3">
                <div class="card-body text-center">
                    <i class="bi bi-shield-lock text-success display-4"></i>
                    <h6 class="mt-2">Compra Segura</h6>
                    <small class="text-muted">
                        Tus datos de pago están protegidos con encriptación SSL
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection