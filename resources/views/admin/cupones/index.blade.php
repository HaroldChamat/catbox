@extends('layouts.app')
@section('title', 'Cupones - Admin')
@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-ticket-perforated"></i> Cupones de descuento</h2>
        <a href="{{ route('admin.cupones.crear') }}" class="btn btn-admin">
            <i class="bi bi-plus-circle"></i> Nuevo cupón
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Alcance</th>
                    <th>Usos</th>
                    <th>Expira</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cupones as $cupon)
                <tr>
                    <td><code class="fs-6">{{ $cupon->codigo }}</code></td>
                    <td>{{ $cupon->tipo === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' }}</td>
                    <td>
                        @if($cupon->tipo === 'porcentaje')
                            <span class="badge bg-primary">{{ $cupon->valor }}%</span>
                        @else
                            <span class="badge bg-success">${{ number_format($cupon->valor, 0, ',', '.') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($cupon->alcance === 'tienda') Toda la tienda
                        @elseif($cupon->alcance === 'categoria') {{ $cupon->categoria->nombre ?? '-' }}
                        @else Productos específicos
                        @endif
                    </td>
                    <td>
                        {{ $cupon->usos_actuales }}
                        @if($cupon->limite_usos) / {{ $cupon->limite_usos }} @endif
                    </td>
                    <td>{{ $cupon->fecha_expiracion ? $cupon->fecha_expiracion->format('d/m/Y') : 'Sin límite' }}</td>
                    <td>
                        @if($cupon->esValido())
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.cupones.toggle', $cupon->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm {{ $cupon->activo ? 'btn-warning' : 'btn-success' }}">
                                {{ $cupon->activo ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.cupones.destruir', $cupon->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar cupón?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No hay cupones creados.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $cupones->links() }}
    </div>
</div>
@endsection