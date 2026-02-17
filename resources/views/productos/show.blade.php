@extends('layouts.app')

@section('title', $producto->nombre . ' - Catbox')

@section('content')
<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('productos.categoria', $producto->categoria->slug) }}">{{ $producto->categoria->nombre }}</a></li>
            <li class="breadcrumb-item active">{{ $producto->nombre }}</li>
        </ol>
    </nav>

    <div class="row">
        {{-- Galería de imágenes --}}
        <div class="col-md-6">
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded shadow">
                    @if($producto->imagenes->count() > 0)
                        @foreach($producto->imagenes as $index => $imagen)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ imagen_o_defecto($imagen->ruta) }}" 
                                 class="d-block w-100" 
                                 alt="{{ $producto->nombre }}"
                                 style="height: 400px; object-fit: cover;"
                                 onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                        </div>
                        @endforeach
                    @else
                    <div class="carousel-item active">
                        <img src="{{ asset('img/NoImagen.jpg') }}" 
                             class="d-block w-100" 
                             alt="{{ $producto->nombre }}"
                             style="height: 400px; object-fit: cover;">
                    </div>
                    @endif
                </div>
                
                @if($producto->imagenes->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span style="
                        background-color: rgba(0,0,0,0.6); 
                        border-radius: 50%; 
                        width: 45px; 
                        height: 45px; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center;
                        border: 2px solid white;
                    ">
                        <span class="carousel-control-prev-icon"></span>
                    </span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                    <span style="
                        background-color: rgba(0,0,0,0.6); 
                        border-radius: 50%; 
                        width: 45px; 
                        height: 45px; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center;
                        border: 2px solid white;
                    ">
                        <span class="carousel-control-next-icon"></span>
                    </span>
                </button>
                @endif
            </div>
            
            {{-- Miniaturas --}}
            @if($producto->imagenes->count() > 1)
            <div class="row mt-3 g-2">
                @foreach($producto->imagenes as $imagen)
                <div class="col-3">
                    <img src="{{ imagen_o_defecto($imagen->ruta) }}" 
                         class="img-thumbnail" 
                         style="cursor: pointer; height: 80px; object-fit: cover; width: 100%;"
                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Información del producto --}}
        <div class="col-md-6">
            <span class="badge bg-secondary mb-2">{{ $producto->categoria->nombre }}</span>
            <h1 class="mb-3">{{ $producto->nombre }}</h1>
            @auth
            <button id="btn-favorito"
                    data-producto-id="{{ $producto->id }}"
                    style="
                        background: {{ $esFavorito ? 'linear-gradient(135deg, #e74c3c, #c0392b)' : 'transparent' }};
                        border: 2px solid #e74c3c;
                        color: {{ $esFavorito ? 'white' : '#e74c3c' }};
                        border-radius: 50px;
                        padding: 8px 20px;
                        font-weight: 700;
                        font-size: 1rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        margin-bottom: 1rem;
                    ">
                <i class="bi {{ $esFavorito ? 'bi-heart-fill' : 'bi-heart' }}" id="icono-favorito" style="font-size: 1.1rem;"></i>
                <span id="texto-favorito">{{ $esFavorito ? 'En favoritos' : 'Agregar a favoritos' }}</span>
            </button>
            @endauth

            {{-- Toast de notificación favoritos --}}
            <div id="toast-favorito" style="
                display: none;
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 9999;
                background: #1a1a2e;
                color: white;
                padding: 14px 22px;
                border-radius: 12px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.3);
                font-weight: 600;
                font-size: 0.95rem;
                align-items: center;
                gap: 10px;
                animation: slideInToast 0.3s ease;
            ">
                <i id="toast-icono" class="bi bi-heart-fill" style="color: #e74c3c; font-size: 1.2rem;"></i>
                <span id="toast-mensaje"></span>
            </div>
            <div class="mb-4">
                <h2 class="text-danger">${{ number_format($producto->precio, 0, ',', '.') }}</h2>
            </div>

            <div class="mb-4">
                <h5>Descripción</h5>
                <p class="text-muted">{{ $producto->descripcion }}</p>
            </div>

            <div class="mb-4">
                <h5>Disponibilidad</h5>
                @if($producto->stock > 0)
                    <p class="text-success">
                        <i class="bi bi-check-circle"></i> 
                        <strong>{{ $producto->stock }}</strong> unidades disponibles
                    </p>
                @else
                    <p class="text-danger">
                        <i class="bi bi-x-circle"></i> Producto agotado
                    </p>
                @endif
            </div>

            @auth
                @if($producto->stock > 0)
                <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST">
                    @csrf
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" 
                                   name="cantidad" 
                                   class="form-control" 
                                   value="1" 
                                   min="1" 
                                   max="{{ $producto->stock }}" 
                                   required>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-catbox btn-lg">
                            <i class="bi bi-cart-plus"></i> Agregar al carrito
                        </button>
                    </div>
                </form>
                @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Este producto está agotado
                </div>
                @endif
            @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                <a href="{{ route('login') }}">Inicia sesión</a> para agregar productos al carrito
            </div>
            @endauth
        </div>
    </div>

    {{-- Productos relacionados --}}
    @if($relacionados->count() > 0)
    <div class="mt-5">
        <h3 class="mb-4">Productos relacionados</h3>
        <div class="row g-4">
            @foreach($relacionados as $relacionado)
            <div class="col-md-3">
                <div class="card card-product shadow-sm h-100">
                    <img src="{{ producto_imagen($relacionado) }}" 
                         class="card-img-top" 
                         alt="{{ $relacionado->nombre }}"
                         style="height: 200px; object-fit: cover;"
                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                    
                    <div class="card-body">
                        <h5 class="card-title">{{ Str::limit($relacionado->nombre, 30) }}</h5>
                        <h4 class="text-danger">${{ number_format($relacionado->precio, 0, ',', '.') }}</h4>
                        <a href="{{ route('productos.show', $relacionado->id) }}" class="btn btn-sm btn-outline-danger w-100">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Reseñas del producto --}}
    <div class="mt-5">
        <h3 class="mb-3">Reseñas del producto</h3>

        {{-- Promedio --}}
        @php $promedio = round($producto->promedioCalificacion(), 1); @endphp
        @if($producto->resenasAprobadas->count() > 0)
        <div class="d-flex align-items-center mb-4">
            <span style="font-size: 2.5rem; font-weight: bold;">{{ $promedio }}</span>
            <div class="ms-3">
                <div style="color: #f39c12; font-size: 1.4rem;">
                    @for($i = 1; $i <= 5; $i++)
                        {{ $i <= round($promedio) ? '★' : '☆' }}
                    @endfor
                </div>
                <small class="text-muted">{{ $producto->resenasAprobadas->count() }} reseñas</small>
            </div>
        </div>
        @endif

        {{-- Listado de reseñas aprobadas --}}
        @forelse($producto->resenasAprobadas()->with('user')->latest()->get() as $resena)
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>{{ $resena->user->name }}</strong>
                    <span class="ms-2" style="color: #f39c12;">{{ $resena->estrellas() }}</span>
                    @auth
                        @if(auth()->id() === $resena->user_id)
                        <button class="btn btn-sm btn-link p-0 ms-2 text-muted" onclick="toggleFormEditar()" title="Editar reseña">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        @endif
                    @endauth
                    </div>
                    <small class="text-muted">{{ $resena->created_at->diffForHumans() }}</small>
                </div>
                <p class="mt-2 mb-0">{{ $resena->comentario }}</p>
            </div>
        </div>
        @empty
        <p class="text-muted">Aún no hay reseñas para este producto.</p>
        @endforelse

        {{-- Formulario para escribir reseña --}}
        @auth
            @if($puedeResenaear)
                @if($yaReseno)
                @php
                    $miResena = $producto->resenas()->where('user_id', auth()->id())->first();
                @endphp
                <div class="alert alert-info mt-4 d-flex align-items-center justify-content-between">
                    <span>
                        @if($miResena->estado === 'pendiente')
                            <i class="bi bi-clock"></i> Tu reseña está <strong>pendiente de aprobación</strong>
                        @elseif($miResena->estado === 'aprobada')
                            <i class="bi bi-check-circle text-success"></i> Tu reseña está <strong>aprobada</strong>
                        @else
                            <i class="bi bi-x-circle text-danger"></i> Tu reseña fue <strong>rechazada</strong>. Puedes editarla y reenviarla.
                        @endif
                    </span>
                    <button class="btn btn-sm btn-outline-secondary ms-3" onclick="toggleFormEditar()">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                </div>

                {{-- Formulario de edición oculto --}}
                <div id="form-editar-resena" style="display:none;">
                    <div class="card mt-2 shadow-sm">
                        <div class="card-header"><strong>Editar tu reseña</strong></div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger">{{ $errors->first() }}</div>
                            @endif
                            <form action="{{ route('resenas.editar', $producto->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label">Calificación</label>
                                    <div class="estrellas-input estrellas-editar" style="font-size: 2rem; cursor: pointer;">
                                        @for($i = 1; $i <= 5; $i++)
                                        <span class="estrella-editar" data-valor="{{ $i }}"
                                            style="color: {{ $i <= $miResena->calificacion ? '#f39c12' : '#ccc' }}">★</span>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="calificacion" id="calificacion-editar" value="{{ $miResena->calificacion }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Comentario</label>
                                    <textarea name="comentario" class="form-control" rows="3"
                                            maxlength="500" required>{{ $miResena->comentario }}</textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-catbox">
                                        <i class="bi bi-check-lg"></i> Guardar cambios
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleFormEditar()">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <div class="card mt-4 shadow-sm">
                    <div class="card-header"><strong>Escribe tu reseña</strong></div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif
                        <form action="{{ route('resenas.guardar', $producto->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Calificación</label>
                                <div class="estrellas-input" style="font-size: 2rem; cursor: pointer; color: #ccc;">
                                    @for($i = 1; $i <= 5; $i++)
                                    <span class="estrella" data-valor="{{ $i }}">★</span>
                                    @endfor
                                </div>
                                <input type="hidden" name="calificacion" id="calificacion-input" value="">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comentario</label>
                                <textarea name="comentario" class="form-control" rows="3"
                                        placeholder="Cuéntanos tu experiencia con este producto..."
                                        maxlength="500" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-catbox">Enviar reseña</button>
                        </form>
                    </div>
                </div>
                @endif
            @endif
        @endauth
    </div>
    @push('scripts')
        <script>
        // ---- FAVORITOS ----
        const btnFavorito = document.getElementById('btn-favorito');
        const toastFavorito = document.getElementById('toast-favorito');

        function mostrarToast(mensaje, agregado) {
            const icono = document.getElementById('toast-icono');
            const mensajeEl = document.getElementById('toast-mensaje');

            icono.className = agregado ? 'bi bi-heart-fill' : 'bi bi-heart';
            icono.style.color = agregado ? '#e74c3c' : '#aaa';
            mensajeEl.textContent = mensaje;

            toastFavorito.style.display = 'flex';
            toastFavorito.style.opacity = '1';

            clearTimeout(toastFavorito._timeout);
            toastFavorito._timeout = setTimeout(() => {
                toastFavorito.style.transition = 'opacity 0.4s ease';
                toastFavorito.style.opacity = '0';
                setTimeout(() => { toastFavorito.style.display = 'none'; }, 400);
            }, 2500);
        }

        if (btnFavorito) {
            btnFavorito.addEventListener('click', function () {
                const productoId = this.dataset.productoId;
                const icono = document.getElementById('icono-favorito');
                const texto = document.getElementById('texto-favorito');

                btnFavorito.style.transform = 'scale(0.92)';
                setTimeout(() => { btnFavorito.style.transform = 'scale(1)'; }, 150);

                fetch(`/favoritos/${productoId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.esFavorito) {
                        btnFavorito.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
                        btnFavorito.style.color = 'white';
                        btnFavorito.style.borderColor = '#c0392b';
                        icono.className = 'bi bi-heart-fill';
                        icono.style.transition = 'transform 0.2s ease';
                        icono.style.transform = 'scale(1.4)';
                        setTimeout(() => { icono.style.transform = 'scale(1)'; }, 200);
                        texto.textContent = 'En favoritos';
                        mostrarToast('¡Agregado a favoritos!', true);
                    } else {
                        btnFavorito.style.background = 'transparent';
                        btnFavorito.style.color = '#e74c3c';
                        btnFavorito.style.borderColor = '#e74c3c';
                        icono.className = 'bi bi-heart';
                        texto.textContent = 'Agregar a favoritos';
                        mostrarToast('Eliminado de favoritos', false);
                    }
                })
                .catch(() => {
                    mostrarToast('Error al actualizar favoritos', false);
                });
            });
        }

        // ---- ESTRELLAS (nueva reseña) ----
        const estrellas = document.querySelectorAll('.estrella');
        const inputCalificacion = document.getElementById('calificacion-input');

        estrellas.forEach(estrella => {
            estrella.addEventListener('mouseover', function () {
                const valor = this.dataset.valor;
                estrellas.forEach(e => {
                    e.style.color = e.dataset.valor <= valor ? '#f39c12' : '#ccc';
                });
            });
            estrella.addEventListener('click', function () {
                inputCalificacion.value = this.dataset.valor;
                estrellas.forEach(e => {
                    e.style.color = e.dataset.valor <= this.dataset.valor ? '#f39c12' : '#ccc';
                });
            });
        });

        document.querySelector('.estrellas-input')?.addEventListener('mouseleave', function () {
            const valorSeleccionado = inputCalificacion.value;
            estrellas.forEach(e => {
                e.style.color = valorSeleccionado && e.dataset.valor <= valorSeleccionado ? '#f39c12' : '#ccc';
            });
        });

        // ---- TOGGLE FORM EDITAR ----
        function toggleFormEditar() {
            const form = document.getElementById('form-editar-resena');
            if (!form) return;
            const visible = form.style.display !== 'none';
            form.style.display = visible ? 'none' : 'block';
            if (!visible) {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // ---- ESTRELLAS (editar reseña) ----
        const estrellasEditar = document.querySelectorAll('.estrella-editar');
        const inputCalificacionEditar = document.getElementById('calificacion-editar');

        estrellasEditar.forEach(estrella => {
            estrella.addEventListener('mouseover', function () {
                const valor = this.dataset.valor;
                estrellasEditar.forEach(e => {
                    e.style.color = e.dataset.valor <= valor ? '#f39c12' : '#ccc';
                });
            });
            estrella.addEventListener('click', function () {
                inputCalificacionEditar.value = this.dataset.valor;
                estrellasEditar.forEach(e => {
                    e.style.color = e.dataset.valor <= this.dataset.valor ? '#f39c12' : '#ccc';
                });
            });
        });

        document.querySelector('.estrellas-editar')?.addEventListener('mouseleave', function () {
            const valorSeleccionado = inputCalificacionEditar?.value;
            estrellasEditar.forEach(e => {
                e.style.color = valorSeleccionado && e.dataset.valor <= valorSeleccionado ? '#f39c12' : '#ccc';
            });
        });

        // Si hay errores de validación, abrir el form de editar automáticamente
        @if($errors->any() && isset($miResena))
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('form-editar-resena');
                if (form) form.style.display = 'block';
            });
        @endif
        </script>
    @endpush
</div>
@endsection