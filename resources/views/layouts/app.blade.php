<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Catbox - Tu tienda de coleccionables')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.bunny.net/css?family=Nunito:300,400,600,700,800" rel="stylesheet">

    @stack('styles')

    <style>
        body { font-family: 'Nunito', sans-serif; background: #f8f9fa; }

        /* ── Navbar ── */
        .navbar-catbox { background: #1a1a2e !important; }
        .navbar-catbox .navbar-brand { font-weight: 800; font-size: 1.5rem; color: #ff6b6b !important; letter-spacing: 1px; }
        .navbar-catbox .nav-link { color: rgba(255,255,255,.8) !important; font-weight: 600; transition: color .2s; }
        .navbar-catbox .nav-link:hover { color: #ff6b6b !important; }
        .navbar-catbox .nav-link.active { color: #ff6b6b !important; }

        /* Navbar admin */
        .navbar-admin { background: #0f3460 !important; }
        .navbar-admin .navbar-brand { color: #e94560 !important; }
        .navbar-admin .nav-link { color: rgba(255,255,255,.85) !important; }
        .navbar-admin .nav-link:hover { color: #e94560 !important; }
        .badge-admin { background: #e94560; font-size: .7rem; }

        /* ── Botones ── */
        .btn-catbox { background: #ff6b6b; border: none; color: white; font-weight: 700; }
        .btn-catbox:hover { background: #ee5a52; color: white; transform: translateY(-1px); }
        .btn-admin { background: #e94560; border: none; color: white; font-weight: 700; }
        .btn-admin:hover { background: #c73652; color: white; }

        /* ── Cards ── */
        .card-product { transition: transform .25s, box-shadow .25s; height: 100%; border: none; }
        .card-product:hover { transform: translateY(-6px); box-shadow: 0 8px 25px rgba(0,0,0,.12); }

        /* ── Carrito badge ── */
        .cart-badge { position: absolute; top: -7px; right: -7px; font-size: .65rem; }

        /* ── Flash messages ── */
        .alert-float { position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn .3s ease; }
        @keyframes slideIn { from { opacity:0; transform:translateX(50px); } to { opacity:1; transform:translateX(0); } }
    </style>
</head>
<body>
<div id="app">

    {{-- ═══════════════════════════════════════
         NAVBAR ADMIN
    ═══════════════════════════════════════ --}}
    @auth
    @if(auth()->user()->esAdmin())
    <nav class="navbar navbar-expand-lg navbar-admin shadow sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('landing') }}">
                <i class="bi bi-box-seam"></i> Catbox
                <span class="badge badge-admin ms-1">ADMIN</span>
            </a>
            <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
                <i class="bi bi-list text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navAdmin">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('productos.index') }}">
                            <i class="bi bi-shop"></i> Tienda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.resenas.index') }}">
                            <i class="bi bi-star-half"></i> Reseñas
                            @php $pendientesCount = \App\Models\Resena::where('estado', 'pendiente')->count(); @endphp
                            @if($pendientesCount > 0)
                                <span class="badge bg-danger">{{ $pendientesCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.cupones.index') }}">
                            <i class="bi bi-ticket-perforated"></i> Cupones
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav align-items-lg-center gap-2">
                    {{-- Botón Panel de Control --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-admin btn-sm px-3">
                            <i class="bi bi-speedometer2"></i> Panel de Control
                        </a>
                    </li>
                    {{-- Botón Agregar Producto --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.productos.crear') }}" class="btn btn-outline-light btn-sm px-3">
                            <i class="bi bi-plus-circle"></i> Nuevo Producto
                        </a>
                    </li>
                    {{-- Dropdown usuario --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown">
                            <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:32px;height:32px">
                                <i class="bi bi-person-fill text-white" style="font-size:.9rem"></i>
                            </div>
                            <span>{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><span class="dropdown-item-text text-muted small">{{ auth()->user()->email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right text-danger"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- ═══════════════════════════════════════
         NAVBAR USUARIO NORMAL
    ═══════════════════════════════════════ --}}
    @else
    <nav class="navbar navbar-expand-lg navbar-catbox shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('landing') }}">
                <i class="bi bi-box-seam"></i> Catbox
            </a>
            <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#navUser">
                <i class="bi bi-list text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navUser">
                {{-- Buscador --}}
                <form class="d-flex mx-auto my-2 my-lg-0" action="{{ route('productos.buscar') }}" method="GET" style="width:100%;max-width:350px">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="Buscar productos..." value="{{ request('q') }}">
                        <button class="btn btn-catbox" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('productos.index') }}"><i class="bi bi-grid"></i> Catálogo</a></li>

                    {{-- Notificaciones --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" id="btn-notificaciones">
                            <i class="bi bi-bell fs-5"></i>
                            @php $noLeidas = auth()->user()->unreadNotifications->count(); @endphp
                            @if($noLeidas > 0)
                                <span class="badge bg-danger cart-badge">{{ $noLeidas }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <li class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
                                <strong>Notificaciones</strong>
                                @if($noLeidas > 0)
                                <button class="btn btn-link btn-sm p-0 text-muted" id="btn-marcar-leidas">
                                    Marcar todas como leídas
                                </button>
                                @endif
                            </li>

                            @forelse(auth()->user()->notifications()->latest()->take(10)->get() as $notif)
                            <li class="px-3 py-2 border-bottom {{ $notif->read_at ? '' : 'bg-light' }}">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="mt-1">
                                        @if(isset($notif->data['codigo']))
                                            <i class="bi bi-ticket-perforated-fill text-success"></i>
                                        @else
                                            <i class="bi bi-bell text-primary"></i>
                                        @endif
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="fw-bold small">{{ $notif->data['titulo'] ?? 'Notificación' }}</div>
                                        <div class="small text-muted">{{ $notif->data['mensaje'] ?? '' }}</div>
                                        @if(isset($notif->data['codigo']))
                                        <div class="mt-1">
                                            <code class="bg-success text-white px-2 py-1 rounded small">
                                                {{ $notif->data['codigo'] }}
                                            </code>
                                        </div>
                                        @endif
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            {{ $notif->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    @if(!$notif->read_at)
                                        <div class="bg-danger rounded-circle mt-1" style="width:8px;height:8px;flex-shrink:0;"></div>
                                    @endif
                                </div>
                            </li>
                            @empty
                            <li class="px-3 py-4 text-center text-muted">
                                <i class="bi bi-bell-slash display-6"></i>
                                <p class="mt-2 mb-0 small">No tienes notificaciones</p>
                            </li>
                            @endforelse
                        </ul>
                    </li>

                    {{-- Carrito --}}
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('carrito.index') }}">
                            <i class="bi bi-cart3 fs-5"></i>
                            @if(auth()->user()->carrito && auth()->user()->carrito->totalProductos() > 0)
                                <span class="badge bg-danger cart-badge">
                                    {{ auth()->user()->carrito->totalProductos() }}
                                </span>
                            @endif
                        </a>
                    </li>
                    {{-- Dropdown usuario --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown">
                            <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:32px;height:32px">
                                <i class="bi bi-person-fill text-white" style="font-size:.9rem"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><span class="dropdown-item-text fw-bold">{{ auth()->user()->name }}</span></li>
                            <li><span class="dropdown-item-text text-muted small">{{ auth()->user()->email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('perfil.index') }}"><i class="bi bi-person"></i> Mi cuenta</a></li>
                            <li><a class="dropdown-item" href="{{ route('ordenes.index') }}"><i class="bi bi-bag"></i> Mis órdenes</a></li>
                            <li><a class="dropdown-item" href="{{ route('favoritos.index') }}"><i class="bi bi-heart-fill text-danger"></i> Mis favoritos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endif

    {{-- ═══════════════════════════════════════
         NAVBAR VISITANTE (no logueado)
    ═══════════════════════════════════════ --}}
    @else
    <nav class="navbar navbar-expand-lg navbar-catbox shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('landing') }}">
                <i class="bi bi-box-seam"></i> Catbox
            </a>
            <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#navGuest">
                <i class="bi bi-list text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navGuest">
                <form class="d-flex mx-auto my-2 my-lg-0" action="{{ route('productos.buscar') }}" method="GET" style="width:100%;max-width:350px">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="Buscar productos..." value="{{ request('q') }}">
                        <button class="btn btn-catbox" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('productos.index') }}"><i class="bi bi-grid"></i> Catálogo</a></li>
                    <li class="nav-item"><a class="btn btn-outline-light btn-sm px-3" href="{{ route('login') }}">Iniciar sesión</a></li>
                    <li class="nav-item"><a class="btn btn-catbox btn-sm px-3" href="{{ route('register') }}">Registrarse</a></li>
                </ul>
            </div>
        </div>
    </nav>
    @endauth

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible alert-float shadow" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible alert-float shadow" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Contenido principal --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5 class="text-danger fw-bold"><i class="bi bi-box-seam"></i> Catbox</h5>
                    <p class="text-muted small">Tu tienda de coleccionables de anime, K-pop y más.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h6 class="fw-bold">Categorías</h6>
                    <ul class="list-unstyled text-muted small">
                        @php
                            $categoriasFooter = \App\Models\CategoriaProducto::orderBy('nombre')->get();
                        @endphp
                        @foreach($categoriasFooter as $cat)
                        <li>
                            <a href="{{ route('productos.categoria', $cat->slug) }}" class="text-muted text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> {{ $cat->nombre }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h6 class="fw-bold">Contacto</h6>
                    <p class="text-muted small"><i class="bi bi-envelope"></i> contacto@catbox.com</p>
                    <p class="text-muted small"><i class="bi bi-instagram"></i> @catboxstore</p>
                </div>
            </div>
            <hr class="border-secondary">
            <p class="text-center text-muted small mb-0">© {{ date('Y') }} Catbox. Todos los derechos reservados.</p>
        </div>
    </footer>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Auto-cerrar alerts --}}
<script>
    setTimeout(() => {
        document.querySelectorAll('.alert-float').forEach(el => {
            new bootstrap.Alert(el).close();
        });
    }, 4000);
</script>

@auth
@if(!auth()->user()->esAdmin())
<script>
const btnMarcarLeidas = document.getElementById('btn-marcar-leidas');
if (btnMarcarLeidas) {
    btnMarcarLeidas.addEventListener('click', function () {
        fetch('{{ route('notificaciones.marcar-leidas') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(() => {
            // Quitar badge
            const badge = document.querySelector('#btn-notificaciones .badge');
            if (badge) badge.remove();
            // Quitar fondo gris de notificaciones
            document.querySelectorAll('.bg-light').forEach(el => el.classList.remove('bg-light'));
            // Quitar puntos rojos
            document.querySelectorAll('.dropdown-menu .bg-danger.rounded-circle').forEach(el => el.remove());
            // Ocultar botón
            btnMarcarLeidas.style.display = 'none';
        });
    });
}

// Marcar como leídas al abrir el dropdown
document.getElementById('btn-notificaciones')?.addEventListener('click', function () {
    const badge = this.querySelector('.badge');
    if (badge) {
        fetch('{{ route('notificaciones.marcar-leidas') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        }).then(() => {
            badge.remove();
        });
    }
});
</script>
@endif
@endauth

@stack('scripts')
</body>
</html>