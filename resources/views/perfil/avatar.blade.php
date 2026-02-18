@extends('layouts.app')
@section('title', 'Avatar - Mi Perfil')

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
                    <h5 class="mb-0"><i class="bi bi-camera me-2"></i>Foto de Perfil</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <div class="text-center mb-4">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" 
                                 class="rounded-circle mb-3" 
                                 style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #ff6b6b;"
                                 alt="Avatar">
                        @else
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 200px; height: 200px; border: 4px solid #ff6b6b;">
                                <i class="bi bi-person-fill text-white" style="font-size: 5rem;"></i>
                            </div>
                        @endif

                        @if(auth()->user()->avatar)
                        <div>
                            <form action="{{ route('perfil.avatar.eliminar') }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar avatar?')">
                                    <i class="bi bi-trash"></i> Eliminar avatar
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>

                    <hr>

                    <form action="{{ route('perfil.avatar.actualizar') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Selecciona una nueva imagen</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*" required>
                            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                        </div>

                        <button type="submit" class="btn btn-catbox">
                            <i class="bi bi-upload"></i> Subir nueva foto
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection