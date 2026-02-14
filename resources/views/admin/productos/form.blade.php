@extends('layouts.app')
@section('title', isset($producto) ? 'Editar producto' : 'Nuevo producto')

@push('styles')
<style>
    .img-preview-wrap {
        position: relative;
        display: inline-block;
        margin: 5px;
    }
    .img-preview-wrap img {
        width: 100px; height: 100px;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #dee2e6;
    }
    .img-preview-wrap .btn-del-img {
        position: absolute;
        top: -8px; right: -8px;
        background: #dc3545;
        border: none;
        color: white;
        border-radius: 50%;
        width: 24px; height: 24px;
        font-size: .7rem;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        line-height: 1;
    }
    .drop-zone {
        border: 2px dashed #dee2e6;
        border-radius: 16px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: border-color .3s, background .3s;
    }
    .drop-zone:hover, .drop-zone.dragover {
        border-color: #ff6b6b;
        background: #fff5f5;
    }
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
                <li><a class="nav-link active" href="{{ route('admin.productos.index') }}"><i class="bi bi-box-seam me-2"></i>Productos</a></li>
            </ul>
        </div>

        {{-- Contenido --}}
        <div class="col-lg-10 py-4 px-4">

            {{-- Encabezado --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h3 class="fw-800 mb-0">
                        {{ isset($producto) ? 'Editar producto' : 'Nuevo producto' }}
                    </h3>
                    <small class="text-muted">
                        {{ isset($producto) ? 'Modifica la información del producto' : 'Completa todos los campos para agregar un producto' }}
                    </small>
                </div>
            </div>

            <form action="{{ isset($producto) ? route('admin.productos.actualizar', $producto->id) : route('admin.productos.guardar') }}"
                  method="POST" enctype="multipart/form-data" id="formProducto">
                @csrf
                @if(isset($producto)) @method('PUT') @endif

                <div class="row g-4">

                    {{-- ── Columna principal ── --}}
                    <div class="col-lg-8">

                        {{-- Info básica --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-700 mb-4"><i class="bi bi-info-circle text-danger me-2"></i>Información básica</h5>

                                <div class="mb-3">
                                    <label class="form-label fw-600">Nombre del producto <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                                           value="{{ old('nombre', $producto->nombre ?? '') }}"
                                           placeholder="Ej: Nendoroid Gojo Satoru">
                                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-600">Descripción</label>
                                    <textarea name="descripcion" rows="4"
                                              class="form-control @error('descripcion') is-invalid @enderror"
                                              placeholder="Describe el producto, incluye materiales, tamaño, serie, etc.">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
                                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Imágenes --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-700 mb-1"><i class="bi bi-images text-danger me-2"></i>Imágenes del producto</h5>
                                <p class="text-muted small mb-4">La primera imagen que subas será la principal. Puedes subir hasta 6 imágenes.</p>

                                {{-- Imágenes existentes (modo edición) --}}
                                @if(isset($producto) && $producto->imagenes->count() > 0)
                                <div class="mb-3">
                                    <p class="fw-600 small text-muted mb-2">IMÁGENES ACTUALES</p>
                                    <div class="d-flex flex-wrap">
                                        @foreach($producto->imagenes as $img)
                                        <div class="img-preview-wrap">
                                            <img src="{{ imagen_o_defecto($img->ruta) }}"
                                                 onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                            @if($img->es_principal)
                                                <span class="position-absolute bottom-0 start-0 m-1 badge bg-success" style="font-size:.6rem">Principal</span>
                                            @endif
                                            {{-- BOTÓN DE ELIMINAR SIN FORM ANIDADO --}}
                                            <button type="button" 
                                                    class="btn-del-img" 
                                                    title="Eliminar imagen"
                                                    onclick="eliminarImagen({{ $producto->id }}, {{ $img->id }})">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                {{-- Drop zone --}}
                                <div class="drop-zone" id="dropZone" onclick="document.getElementById('imagenes').click()">
                                    <i class="bi bi-cloud-upload display-4 text-muted mb-2 d-block"></i>
                                    <p class="fw-600 mb-1">Haz clic o arrastra imágenes aquí</p>
                                    <small class="text-muted">JPG, PNG, WEBP — máximo 2MB por imagen</small>
                                    <input type="file" id="imagenes" name="imagenes[]"
                                           multiple accept="image/*" class="d-none"
                                           onchange="previewImages(this)">
                                </div>

                                {{-- Vista previa nuevas --}}
                                <div id="previewContainer" class="d-flex flex-wrap mt-3"></div>

                                @error('imagenes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @error('imagenes.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    </div>

                    {{-- ── Columna lateral ── --}}
                    <div class="col-lg-4">

                        {{-- Precio y stock --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-700 mb-4"><i class="bi bi-tag text-danger me-2"></i>Precio y stock</h5>

                                <div class="mb-3">
                                    <label class="form-label fw-600">Precio (COP) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="precio" min="0" step="100"
                                               class="form-control @error('precio') is-invalid @enderror"
                                               value="{{ old('precio', $producto->precio ?? '') }}"
                                               placeholder="0">
                                    </div>
                                    @error('precio') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-600">Stock disponible <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" min="0"
                                           class="form-control @error('stock') is-invalid @enderror"
                                           value="{{ old('stock', $producto->stock ?? '') }}"
                                           placeholder="0">
                                    @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Categoría --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-700 mb-4"><i class="bi bi-collection text-danger me-2"></i>Categoría</h5>

                                <div class="mb-3">
                                    <label class="form-label fw-600">Categoría <span class="text-danger">*</span></label>
                                    <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                                        <option value="">Seleccionar...</option>
                                        @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ old('categoria_id', $producto->categoria_id ?? '') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Estado --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-700 mb-3"><i class="bi bi-toggles text-danger me-2"></i>Estado</h5>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" name="activo" value="1" id="activoSwitch"
                                           {{ old('activo', $producto->activo ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-600" for="activoSwitch">Producto activo</label>
                                </div>
                                <small class="text-muted">Los productos inactivos no se muestran en la tienda</small>
                            </div>
                        </div>

                        {{-- Guardar --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-admin btn-lg">
                                <i class="bi bi-{{ isset($producto) ? 'check-circle' : 'plus-circle' }}"></i>
                                {{ isset($producto) ? 'Guardar cambios' : 'Crear producto' }}
                            </button>
                            <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- FORMULARIO OCULTO PARA ELIMINAR IMÁGENES --}}
<form id="formEliminarImagen" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    // Preview imágenes nuevas
    function previewImages(input) {
        const container = document.getElementById('previewContainer');
        container.innerHTML = '';
        Array.from(input.files).forEach((file, i) => {
            const reader = new FileReader();
            reader.onload = e => {
                const wrap = document.createElement('div');
                wrap.style.cssText = 'position:relative;display:inline-block;margin:5px';
                wrap.innerHTML = `
                    <img src="${e.target.result}"
                         style="width:100px;height:100px;object-fit:cover;border-radius:10px;border:2px solid #dee2e6">
                    ${i === 0 ? '<span style="position:absolute;bottom:5px;left:5px;background:#198754;color:white;font-size:.6rem;padding:2px 6px;border-radius:10px">Principal</span>' : ''}
                `;
                container.appendChild(wrap);
            };
            reader.readAsDataURL(file);
        });
    }

    // Drag and drop
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const input = document.getElementById('imagenes');
        input.files = e.dataTransfer.files;
        previewImages(input);
    });

    // Función para eliminar imagen SIN form anidado
    function eliminarImagen(productoId, imagenId) {
        if (!confirm('¿Eliminar esta imagen?')) {
            return;
        }

        const form = document.getElementById('formEliminarImagen');
        form.action = `/admin/productos/${productoId}/imagen/${imagenId}`;
        form.submit();
    }
</script>
@endpush