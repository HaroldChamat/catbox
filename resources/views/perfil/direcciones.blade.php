@extends('layouts.app')
@section('title', 'Direcciones - Mi Perfil')

@section('content')
<div class="container my-5">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-3">
            @include('perfil.partials.sidebar')
        </div>

        {{-- Contenido --}}
        <div class="col-md-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Mis Direcciones</h5>
                    <button class="btn btn-sm btn-catbox" data-bs-toggle="modal" data-bs-target="#modalNuevaDireccion">
                        <i class="bi bi-plus-circle"></i> Nueva dirección
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    @forelse($direcciones as $dir)
                    <div class="card mb-3 {{ $dir->es_principal ? 'border-success' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    @if($dir->es_principal)
                                        <span class="badge bg-success mb-2">Principal</span>
                                    @endif
                                    <h6 class="mb-2">{{ $dir->direccion }}</h6>
                                    <p class="mb-1 text-muted"><i class="bi bi-building"></i> {{ $dir->ciudad }}</p>
                                    <p class="mb-1 text-muted"><i class="bi bi-mailbox"></i> CP: {{ $dir->codigo_postal }}</p>
                                    <p class="mb-0 text-muted"><i class="bi bi-telephone"></i> {{ $dir->telefono }}</p>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditarDireccion{{ $dir->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('perfil.direcciones.eliminar', $dir->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('¿Eliminar esta dirección?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal editar dirección --}}
                    <div class="modal fade" id="modalEditarDireccion{{ $dir->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Editar Dirección</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('perfil.direcciones.editar', $dir->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Dirección <span class="text-danger">*</span></label>
                                            <input type="text" name="direccion" class="form-control" value="{{ $dir->direccion }}" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                                                <input type="text" name="ciudad" class="form-control" value="{{ $dir->ciudad }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Código Postal <span class="text-danger">*</span></label>
                                                <input type="text" name="codigo_postal" class="form-control" value="{{ $dir->codigo_postal }}" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                                            <input type="text" name="telefono" class="form-control" value="{{ $dir->telefono }}" required>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="es_principal" value="1" 
                                                   id="principal{{ $dir->id }}" {{ $dir->es_principal ? 'checked' : '' }}>
                                            <label class="form-check-label" for="principal{{ $dir->id }}">
                                                Marcar como dirección principal
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-catbox">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-geo-alt display-4"></i>
                        <p class="mt-2">No tienes direcciones registradas</p>
                        <button class="btn btn-catbox" data-bs-toggle="modal" data-bs-target="#modalNuevaDireccion">
                            <i class="bi bi-plus-circle"></i> Agregar primera dirección
                        </button>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal nueva dirección --}}
<div class="modal fade" id="modalNuevaDireccion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Dirección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('perfil.direcciones.guardar') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Dirección <span class="text-danger">*</span></label>
                        <input type="text" name="direccion" class="form-control" placeholder="Calle, número, apartamento..." required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                            <input type="text" name="ciudad" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código Postal <span class="text-danger">*</span></label>
                            <input type="text" name="codigo_postal" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="text" name="telefono" class="form-control" placeholder="300 123 4567" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="es_principal" value="1" id="principalNueva">
                        <label class="form-check-label" for="principalNueva">
                            Marcar como dirección principal
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-catbox">Guardar dirección</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection