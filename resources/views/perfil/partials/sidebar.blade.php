<div class="card shadow-sm sticky-top" style="top: 80px;">
    <div class="card-body p-0">
        <div class="text-center p-4 border-bottom">
            @if(auth()->user()->avatar)
                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" 
                     class="rounded-circle mb-2" 
                     style="width: 80px; height: 80px; object-fit: cover;"
                     alt="Avatar">
            @else
                <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2"
                     style="width: 80px; height: 80px;">
                    <i class="bi bi-person-fill text-white" style="font-size: 2.5rem;"></i>
                </div>
            @endif
            <h6 class="mb-0">{{ auth()->user()->name }}</h6>
            <small class="text-muted">{{ auth()->user()->email }}</small>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('perfil.index') ? 'active text-danger fw-bold' : 'text-dark' }}" 
                   href="{{ route('perfil.index') }}">
                    <i class="bi bi-person-circle me-2"></i>Información Personal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('perfil.avatar') ? 'active text-danger fw-bold' : 'text-dark' }}" 
                   href="{{ route('perfil.avatar') }}">
                    <i class="bi bi-camera me-2"></i>Foto de Perfil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('perfil.password') ? 'active text-danger fw-bold' : 'text-dark' }}" 
                   href="{{ route('perfil.password') }}">
                    <i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('perfil.direcciones') ? 'active text-danger fw-bold' : 'text-dark' }}" 
                   href="{{ route('perfil.direcciones') }}">
                    <i class="bi bi-geo-alt me-2"></i>Direcciones de Envío
                </a>
            </li>
            <li class="nav-item border-top mt-2 pt-2">
                <a class="nav-link text-dark" href="{{ route('ordenes.index') }}">
                    <i class="bi bi-bag me-2"></i>Mis Órdenes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('favoritos.index') }}">
                    <i class="bi bi-heart me-2"></i>Mis Favoritos
                </a>
            </li>
        </ul>
    </div>
</div>