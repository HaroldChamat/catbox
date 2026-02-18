@extends('layouts.app')
@section('title', 'Mi Perfil')

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
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Información Personal</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('perfil.actualizar') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono', auth()->user()->telefono) }}" placeholder="Ej: 300 123 4567">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de nacimiento</label>
                                <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento', auth()->user()->fecha_nacimiento?->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-catbox">
                            <i class="bi bi-check-lg"></i> Guardar cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection