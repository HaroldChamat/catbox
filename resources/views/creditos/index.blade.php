@extends('layouts.app')
@section('title', 'Mis Créditos')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            @include('perfil.partials.sidebar')
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Mis Créditos</h5>
                </div>
                <div class="card-body text-center py-4">
                    <h1 class="text-success">${{ number_format($saldoTotal, 0, ',', '.') }}</h1>
                    <p class="text-muted">Saldo total disponible</p>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Puedes usar tus créditos en tu próxima compra
                    </small>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Historial de Créditos</h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Origen</th>
                                <th>Monto Original</th>
                                <th>Saldo Disponible</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($creditos as $credito)
                            <tr>
                                <td>{{ $credito->created_at->format('d/m/Y') }}</td>
                                <td>
                                    Devolución 
                                    <a href="{{ route('ordenes.show', $credito->devolucion->orden_id) }}">
                                        {{ $credito->devolucion->orden->numero_orden }}
                                    </a>
                                </td>
                                <td>${{ number_format($credito->monto, 0, ',', '.') }}</td>
                                <td class="fw-bold text-success">${{ number_format($credito->saldo, 0, ',', '.') }}</td>
                                <td>
                                    @if($credito->usado)
                                        <span class="badge bg-secondary">Usado</span>
                                    @elseif($credito->saldo > 0)
                                        <span class="badge bg-success">Disponible</span>
                                    @else
                                        <span class="badge bg-secondary">Agotado</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No tienes créditos aún
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection