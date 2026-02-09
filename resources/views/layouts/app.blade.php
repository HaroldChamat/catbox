<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Catbox - Tu tienda de coleccionables')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=Nunito:300,400,600,700" rel="stylesheet">

    @stack('styles')
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .btn-catbox {
            background-color: #ff6b6b;
            border-color: #ff6b6b;
            color: white;
        }
        .btn-catbox:hover {
            background-color: #ee5a52;
            border-color: #ee5a52;
            color: white;
        }
        .card-product {
            transition: transform 0.2s;
            height: 100%;
        }
        .card-product:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .badge-cart {
            position: absolute;
            top: -8px;
            right: -8px;
        }
    </style>
</head>
<body>
    <div id="app">
        {{-- Navbar --}}
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
            <div class="container">
                <a class="navbar-brand text-danger" href="{{ route('home') }}">
                    <i class="bi bi-box-seam"></i> Catbox
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarMain">
                    {{-- Menú izquierdo --}}
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('productos.index') }}">Productos</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Categorías
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('productos.categoria', 'nendoroid') }}">Nendoroid</a></li>
                                <li><a class="dropdown-item" href="{{ route('productos.categoria', 'photocards') }}">Photocards</a></li>
                                <li><a class="dropdown-item" href="{{ route('productos.categoria', 'llaveros') }}">Llaveros</a></li>
                            </ul>
                        </li>
                    </ul>

                    {{-- Buscador --}}
                    <form class="d-flex me-3" action="{{ route('productos.buscar') }}" method="GET">
                        <div class="input-group">
                            <input type="search" class="form-control" name="q" placeholder="Buscar productos..." value="{{ request('q') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    {{-- Menú derecho --}}
                    <ul class="navbar-nav">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-catbox btn-sm text-white ms-2" href="{{ route('register') }}">
                                    Registrarse
                                </a>
                            </li>
                        @else
                            {{-- Carrito --}}
                            <li class="nav-item position-relative">
                                <a class="nav-link" href="{{ route('carrito.index') }}">
                                    <i class="bi bi-cart3 fs-5"></i>
                                    @if(auth()->user()->carrito && auth()->user()->carrito->totalProductos() > 0)
                                        <span class="badge bg-danger badge-cart">
                                            {{ auth()->user()->carrito->totalProductos() }}
                                        </span>
                                    @endif
                                </a>
                            </li>

                            {{-- Usuario --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                                            <i class="bi bi-speedometer2"></i> Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('ordenes.index') }}">
                                            <i class="bi bi-bag-check"></i> Mis Órdenes
                                        </a>
                                    </li>
                                    @if(Auth::user()->esAdmin())
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="{{ route('admin.dashboard') }}">
                                                <i class="bi bi-shield-lock"></i> Panel Admin
                                            </a>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        {{-- Mensajes Flash --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show m-0 rounded-0" role="alert">
                <div class="container">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show m-0 rounded-0" role="alert">
                <div class="container">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show m-0 rounded-0" role="alert">
                <div class="container">
                    <i class="bi bi-exclamation-triangle"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- Contenido principal --}}
        <main class="py-4">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="bg-dark text-white mt-5 py-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h5><i class="bi bi-box-seam"></i> Catbox</h5>
                        <p class="text-muted">Tu tienda de confianza para Nendoroids, Photocards y Llaveros coleccionables.</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5>Enlaces rápidos</h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('productos.index') }}" class="text-muted text-decoration-none">Productos</a></li>
                            <li><a href="{{ route('productos.categoria', 'nendoroid') }}" class="text-muted text-decoration-none">Nendoroid</a></li>
                            <li><a href="{{ route('productos.categoria', 'photocards') }}" class="text-muted text-decoration-none">Photocards</a></li>
                            <li><a href="{{ route('productos.categoria', 'llaveros') }}" class="text-muted text-decoration-none">Llaveros</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5>Contacto</h5>
                        <p class="text-muted">
                            <i class="bi bi-envelope"></i> info@catbox.com<br>
                            <i class="bi bi-phone"></i> +57 300 123 4567
                        </p>
                    </div>
                </div>
                <hr class="bg-secondary">
                <div class="text-center text-muted">
                    <small>&copy; {{ date('Y') }} Catbox. Todos los derechos reservados.</small>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>