@extends('layouts.app')

@section('title', 'Moderar Reseñas')

@section('content')
<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-star-half"></i> Reseñas pendientes</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($pendientes->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Usuario</th>
                    <th>Calificación</th>
                    <th>Comentario</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendientes as $resena)
                <tr>
                    <td>{{ $resena->producto->nombre }}</td>
                    <td>{{ $resena->user->name }}</td>
                    <td style="color: #f39c12;">{{ $resena->estrellas() }}</td>
                    <td>{{ $resena->comentario }}</td>
                    <td>{{ $resena->created_at->format('d/m/Y') }}</td>
                    <td>
                        <form action="{{ route('admin.resenas.aprobar', $resena->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success">✓ Aprobar</button>
                        </form>
                        <form action="{{ route('admin.resenas.rechazar', $resena->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-warning">✗ Rechazar</button>
                        </form>
                        <form action="{{ route('admin.resenas.destruir', $resena->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Eliminar reseña?')">🗑</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $pendientes->links() }}
    </div>
    @else
    <div class="alert alert-info">No hay reseñas pendientes de moderación.</div>
    @endif
</div>
@endsection