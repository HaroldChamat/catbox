@extends('layouts.app')
@section('title', 'Devoluciones - Admin')

@section('content')
<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-arrow-return-left me-2"></i>Gestión de Devoluciones</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Orden</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devoluciones as $dev)
                <tr>
                    <td>{{ $dev->id }}</td>
                    <td>
                        {{ $dev->user->name }}<br>
                        <small class="text-muted">{{ $dev->user->email }}</small>
                    </td>
                    <td>
                        <a href="{{ route('admin.ordenes.show', $dev->orden_id) }}" target="_blank">
                            {{ $dev->orden->numero_orden }}
                        </a>
                    </td>
                    <td>${{ number_format($dev->monto_total, 0, ',', '.') }}</td>
                    <td>
                        @if($dev->estado === 'pendiente')
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        @elseif($dev->estado === 'aprobada')
                            <span class="badge bg-success">Aprobada</span>
                        @else
                            <span class="badge bg-danger">Rechazada</span>
                        @endif
                    </td>
                    <td>{{ $dev->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.devoluciones.show', $dev->id) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No hay solicitudes de devolución</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{ $devoluciones->links() }}
    </div>
</div>
@endsection