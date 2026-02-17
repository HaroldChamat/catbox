@extends('layouts.app')

@section('title', 'Mis Favoritos - Catbox')

@section('content')
<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-heart-fill text-danger"></i> Mis Favoritos</h2>

    @if($favoritos->count() > 0)
    <div class="row g-4">
        @foreach($favoritos as $favorito)
        <div class="col-md-3">
            <div class="card card-product shadow-sm h-100">
                <div class="position-relative">
                    <img src="{{ producto_imagen($favorito->producto) }}"
                         class="card-img-top"
                         alt="{{ $favorito->producto->nombre }}"
                         style="height: 200px; object-fit: cover;"
                         onerror="this.src='{{ asset('img/NoImagen.jpg') }}'">
                    <button class="btn-favorito position-absolute top-0 end-0 m-2 btn-toggle-favorito"
                            data-producto-id="{{ $favorito->producto->id }}"
                            style="background:none; border:none; font-size:1.4rem; color:#e74c3c;">
                        ❤️
                    </button>
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ Str::limit($favorito->producto->nombre, 30) }}</h5>
                    <h4 class="text-danger">${{ number_format($favorito->producto->precio, 0, ',', '.') }}</h4>
                    <a href="{{ route('productos.show', $favorito->producto->id) }}" class="btn btn-sm btn-outline-danger w-100">
                        Ver detalle
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-5">
        <i class="bi bi-heart" style="font-size: 4rem; color: #ccc;"></i>
        <h4 class="mt-3 text-muted">Aún no tienes favoritos</h4>
        <p class="text-muted">Explora el catálogo y guarda los productos que te gusten</p>
        <a href="{{ route('home') }}" class="btn btn-catbox">Ver productos</a>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.querySelectorAll('.btn-toggle-favorito').forEach(btn => {
    btn.addEventListener('click', function () {
        const productoId = this.dataset.productoId;
        const card = this.closest('.col-md-3');

        fetch(`/favoritos/${productoId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.esFavorito) {
                card.style.transition = 'opacity 0.3s';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
        });
    });
});
</script>
@endpush
@endsection