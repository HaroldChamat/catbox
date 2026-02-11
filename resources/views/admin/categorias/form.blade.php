@extends('layouts.app')
@section('title', isset($categoria) ? 'Editar Categoría' : 'Nueva Categoría')

@push('styles')
<style>
    .admin-sidebar {
        background: #0f3460;
        min-height: calc(100vh - 56px);
        padding: 20px 0;
    }
    .admin-sidebar .nav-link {
        color: rgba(255,255,255,.7);
        padding: 10px 20px;
        border-radius: 0 30px 30px 0;
        margin-right: 15px;
        transition: all .2s;
        font-weight: 600;
    }
    .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active {
        color: white;
        background: rgba(233,69,96,.3);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">

        {{-- Sidebar --}}
        <div class="col-lg-2 d-none d-lg-block admin-sidebar pt-4">
            <p class="text-white-50 small px-3 fw-600 text-uppercase mb-2">Menú Admin</p>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="{{ route('admin.productos.index') }}"><i class="bi bi-box-seam me-2"></i>Productos</a></li>
                <li><a class="nav-link active" href="{{ route('admin.categorias.index') }}"><i class="bi bi-tag me-2"></i>Categorías</a></li>
                <li><a class="nav-link" href="{{ route('admin.ordenes.index') }}"><i class="bi bi-receipt me-2"></i>Órdenes</a></li>
                <li><a class="nav-link" href="{{ route('admin.estadisticas.index') }}"><i class="bi bi-graph-up me-2"></i>Estadísticas</a></li>
            </ul>
        </div>

        {{-- Contenido --}}
        <div class="col-lg-10 py-4 px-4">

            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h3 class="fw-800 mb-0">
                        {{ isset($categoria) ? 'Editar Categoría' : 'Nueva Categoría' }}
                    </h3>
                    <small class="text-muted">
                        {{ isset($categoria) ? 'Modifica la información de la categoría' : 'Completa los campos para crear una nueva categoría' }}
                    </small>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form action="{{ isset($categoria) ? route('admin.categorias.actualizar', $categoria->id) : route('admin.categorias.guardar') }}"
                                  method="POST">
                                @csrf
                                @if(isset($categoria)) @method('PUT') @endif

                                <div class="mb-4">
                                    <label class="form-label fw-600">Nombre de la categoría <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="nombre" 
                                           class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                                           value="{{ old('nombre', $categoria->nombre ?? '') }}"
                                           placeholder="Ej: Nendoroid"
                                           required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">El slug se generará automáticamente</small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-600">Descripción</label>
                                    <textarea name="descripcion" 
                                              rows="4"
                                              class="form-control @error('descripcion') is-invalid @enderror"
                                              placeholder="Describe esta categoría de productos">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-admin btn-lg">
                                        <i class="bi bi-{{ isset($categoria) ? 'check-circle' : 'plus-circle' }}"></i>
                                        {{ isset($categoria) ? 'Guardar Cambios' : 'Crear Categoría' }}
                                    </button>
                                    <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary">
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-700 mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Información</h6>
                            <ul class="small text-muted ps-3">
                                <li class="mb-2">El nombre debe ser único y descriptivo</li>
                                <li class="mb-2">El slug se genera automáticamente del nombre</li>
                                <li class="mb-2">La descripción ayuda a los usuarios a entender la categoría</li>
                                @if(isset($categoria))
                                <li class="mb-2 text-danger">No puedes eliminar una categoría con productos asociados</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection