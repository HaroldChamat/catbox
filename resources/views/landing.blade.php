@extends('layouts.app')
@section('title', 'Catbox - Coleccionables de Anime y K-pop')

@push('styles')
<style>
    .hero {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        min-height: 90vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }
    .hero::before {
        content: '';
        position: absolute;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(255,107,107,.15) 0%, transparent 70%);
        top: -100px; right: -100px;
        border-radius: 50%;
    }
    .hero::after {
        content: '';
        position: absolute;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(233,69,96,.1) 0%, transparent 70%);
        bottom: -50px; left: -50px;
        border-radius: 50%;
    }
    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.1;
        background: linear-gradient(90deg, #ff6b6b, #ffd93d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .hero-subtitle { color: rgba(255,255,255,.75); font-size: 1.2rem; }
    .floating { animation: float 4s ease-in-out infinite; }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-18px)} }

    .cat-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        transition: transform .3s, box-shadow .3s;
        cursor: pointer;
    }
    .cat-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,.15); }
    .cat-nendoroid { background: linear-gradient(135deg, #667eea, #764ba2); }
    .cat-photocards { background: linear-gradient(135deg, #f093fb, #f5576c); }
    .cat-llaveros { background: linear-gradient(135deg, #43e97b, #38f9d7); }

    .section-title { font-weight: 800; font-size: 2rem; }

    .prod-card { border: none; border-radius: 16px; overflow: hidden; transition: transform .3s, box-shadow .3s; }
    .prod-card:hover { transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,.12); }

    .stat-box { border-radius: 16px; padding: 30px; text-align: center; background: white; box-shadow: 0 4px 20px rgba(0,0,0,.06); }

    .wave { position: relative; bottom: 0; left: 0; width: 100%; overflow: hidden; line-height: 0; }
</style>
@endpush

@section('content')

{{-- ═══ HERO ═══ --}}
<section class="hero">
    <div class="container position-relative" style="z-index:2">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-danger mb-3 px-3 py-2">✨ Coleccionables auténticos</span>
                <h1 class="hero-title mb-3">
                    El universo del<br>anime y K-pop<br>en tus manos
                </h1>
                <p class="hero-subtitle mb-4">
                    Nendoroids, photocards oficiales y llaveros únicos.<br>
                    Tu colección empieza aquí.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('productos.index') }}" class="btn btn-catbox btn-lg px-4">
                        <i class="bi bi-grid"></i> Ver catálogo
                    </a>
                    @guest
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-4">
                        Crear cuenta gratis
                    </a>
                    @endguest
                </div>
                {{-- Stats --}}
                <div class="d-flex gap-4 mt-5">
                    <div class="text-white">
                        <div class="fs-4 fw-800 text-warning">{{ \App\Models\Producto::where('activo',true)->count() }}+</div>
                        <small class="text-white-50">Productos</small>
                    </div>
                    <div class="text-white">
                        <div class="fs-4 fw-800 text-warning">100%</div>
                        <small class="text-white-50">Auténtico</small>
                    </div>
                    <div class="text-white">
                        <div class="fs-4 fw-800 text-warning">⭐ 5.0</div>
                        <small class="text-white-50">Valoración</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <div class="floating">
                    <i class="bi bi-box-seam" style="font-size: 12rem; color: rgba(255,107,107,.3);"></i>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ CATEGORÍAS CON CARRUSEL ═══ --}}
<section class="py-6" style="padding:80px 0; background: #f8f9fa;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Explora por categoría</h2>
            <p class="text-muted">Encuentra exactamente lo que buscas</p>
        </div>

        @if($categorias->count() > 3)
        {{-- Carrusel si hay más de 3 categorías --}}
        <div id="categoriasCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($categorias->chunk(3) as $index => $chunk)
                <button type="button" 
                        data-bs-target="#categoriasCarousel" 
                        data-bs-slide-to="{{ $index }}" 
                        class="{{ $index === 0 ? 'active' : '' }}"
                        style="background-color: #ff6b6b;"></button>
                @endforeach
            </div>

            <div class="carousel-inner" style="background: #1a1a2e; border-radius: 20px; padding: 40px 20px;">
                @foreach($categorias->chunk(3) as $index => $chunk)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="row g-4">
                        @foreach($chunk as $catIndex => $cat)
                        <div class="col-md-4">
                            <a href="{{ route('productos.categoria', $cat->slug) }}" class="text-decoration-none">
                                <div class="cat-card {{ 'cat-' . ($catIndex % 3 == 0 ? 'nendoroid' : ($catIndex % 3 == 1 ? 'photocards' : 'llaveros')) }} p-5 text-white text-center">
                                    <i class="bi bi-{{ $catIndex % 3 == 0 ? 'person-badge' : ($catIndex % 3 == 1 ? 'card-image' : 'key') }} display-2 mb-3 d-block floating" style="animation-delay:{{ $catIndex * 0.5 }}s"></i>
                                    <h3 class="fw-800 mb-2">{{ $cat->nombre }}</h3>
                                    <p class="mb-0 opacity-75">{{ $cat->descripcion ?? 'Descubre nuestra colección' }}</p>
                                    <span class="badge bg-white text-dark mt-3">Ver colección →</span>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#categoriasCarousel" data-bs-slide="prev" style="filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));">
                <span class="carousel-control-prev-icon" style="background-color: rgba(0,0,0,0.7); border-radius: 50%; padding: 20px;"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#categoriasCarousel" data-bs-slide="next" style="filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));">
                <span class="carousel-control-next-icon" style="background-color: rgba(0,0,0,0.7); border-radius: 50%; padding: 20px;"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
        @else
        {{-- Grid estático si hay 3 o menos categorías --}}
        <div class="row g-4">
            @foreach($categorias as $catIndex => $cat)
            <div class="col-md-4">
                <a href="{{ route('productos.categoria', $cat->slug) }}" class="text-decoration-none">
                    <div class="cat-card {{ 'cat-' . ($catIndex % 3 == 0 ? 'nendoroid' : ($catIndex % 3 == 1 ? 'photocards' : 'llaveros')) }} p-5 text-white text-center">
                        <i class="bi bi-{{ $catIndex % 3 == 0 ? 'person-badge' : ($catIndex % 3 == 1 ? 'card-image' : 'key') }} display-2 mb-3 d-block floating" style="animation-delay:{{ $catIndex * 0.5 }}s"></i>
                        <h3 class="fw-800 mb-2">{{ $cat->nombre }}</h3>
                        <p class="mb-0 opacity-75">{{ $cat->descripcion ?? 'Descubre nuestra colección' }}</p>
                        <span class="badge bg-white text-dark mt-3">Ver colección →</span>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

{{-- ═══ PRODUCTOS DESTACADOS ═══ --}}
<section style="padding:80px 0; background: white;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="section-title mb-1">Novedades</h2>
                <p class="text-muted mb-0">Los últimos productos en llegar</p>
            </div>
            <a href="{{ route('productos.index') }}" class="btn btn-catbox px-4">
                Ver todos <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @if($productosDestacados->count() > 0)
        <div id="productosCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($productosDestacados->chunk(4) as $index => $chunk)
                <button type="button" 
                        data-bs-target="#productosCarousel" 
                        data-bs-slide-to="{{ $index }}" 
                        class="{{ $index === 0 ? 'active' : '' }}"
                        style="background-color: #ff6b6b;"></button>
                @endforeach
            </div>

            <div class="carousel-inner" style="background: #1a1a2e; border-radius: 20px; padding: 40px 20px;">
                @foreach($productosDestacados->chunk(4) as $index => $chunk)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="row g-4 px-3">
                        @foreach($chunk as $producto)
                        <div class="col-lg-3 col-md-6">
                            <div class="prod-card card h-100 shadow-sm">
                                <div class="position-relative">
                                    <img src="{{ producto_imagen($producto) }}"
                                         alt="{{ $producto->nombre }}"
                                         style="height:220px;object-fit:cover;width:100%"
                                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                                    <span class="position-absolute top-0 start-0 m-2 badge"
                                          style="background:{{ ['#667eea','#f5576c','#43e97b'][($loop->parent->index * 4 + $loop->index) % 3] }}">
                                        {{ $producto->categoria->nombre }}
                                    </span>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="fw-700 mb-1">{{ Str::limit($producto->nombre, 40) }}</h6>
                                    <p class="text-muted small flex-grow-1">{{ Str::limit($producto->descripcion, 60) }}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="fw-800 text-danger fs-5">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-sm btn-catbox">Ver más</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#productosCarousel" data-bs-slide="prev" style="filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));">
                <span class="carousel-control-prev-icon" style="background-color: rgba(0,0,0,0.8); border-radius: 50%; padding: 20px; width: 50px; height: 50px;"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productosCarousel" data-bs-slide="next" style="filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));">
                <span class="carousel-control-next-icon" style="background-color: rgba(0,0,0,0.8); border-radius: 50%; padding: 20px; width: 50px; height: 50px;"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="text-muted mt-2">No hay productos disponibles aún</p>
        </div>
        @endif
    </div>
</section>

{{-- ═══ BENEFICIOS ═══ --}}
<section style="padding:80px 0; background: #f8f9fa;">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <i class="bi bi-truck display-4 text-primary mb-3 d-block"></i>
                    <h6 class="fw-700">Envío rápido</h6>
                    <small class="text-muted">3-5 días hábiles</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <i class="bi bi-patch-check display-4 text-success mb-3 d-block"></i>
                    <h6 class="fw-700">100% Auténtico</h6>
                    <small class="text-muted">Garantía de origen</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <i class="bi bi-shield-lock display-4 text-warning mb-3 d-block"></i>
                    <h6 class="fw-700">Pago seguro</h6>
                    <small class="text-muted">SSL certificado</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <i class="bi bi-arrow-return-left display-4 text-danger mb-3 d-block"></i>
                    <h6 class="fw-700">Devoluciones</h6>
                    <small class="text-muted">30 días sin preguntas</small>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ CTA ═══ --}}
@guest
<section style="padding:80px 0; background: linear-gradient(135deg, #1a1a2e, #0f3460);">
    <div class="container text-center">
        <h2 class="text-white fw-800 mb-3">¿Listo para empezar tu colección?</h2>
        <p class="text-white-50 mb-4 fs-5">Crea tu cuenta gratis y accede a todos nuestros productos</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="{{ route('register') }}" class="btn btn-catbox btn-lg px-5">
                <i class="bi bi-person-plus"></i> Crear cuenta gratis
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-5">
                Iniciar sesión
            </a>
        </div>
    </div>
</section>
@endguest

@endsection