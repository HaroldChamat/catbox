@extends('layouts.app')
@section('title', 'Cambiar Contraseña - Mi Perfil')

@section('content')
<div class="container my-5">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-3">
            @include('perfil.partials.sidebar')
        </div>

        {{-- Contenido --}}
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('perfil.password.actualizar') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Contraseña actual <span class="text-danger">*</span></label>
                            <input type="password" name="password_actual" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                            <small class="text-muted">Mínimo 8 caracteres</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar nueva contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-catbox">
                            <i class="bi bi-check-lg"></i> Actualizar contraseña
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Consejos de seguridad:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Usa una contraseña única que no uses en otros sitios</li>
                            <li>Combina letras mayúsculas, minúsculas, números y símbolos</li>
                            <li>Evita usar información personal obvia</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection