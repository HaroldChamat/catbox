@extends('layouts.app')
@section('title', 'Crear Cupón')
@section('content')
<div class="container my-5" style="max-width: 700px;">
    <h2 class="mb-4"><i class="bi bi-ticket-perforated"></i> Crear cupón</h2>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('admin.cupones.guardar') }}" method="POST">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">Datos del cupón</div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Código</label>
                    <div class="input-group">
                        <input type="text" name="codigo" class="form-control text-uppercase"
                               value="{{ old('codigo', $codigoSugerido) }}" required>
                        <button type="button" class="btn btn-outline-secondary" id="btn-regenerar">
                            <i class="bi bi-arrow-clockwise"></i> Regenerar
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de descuento</label>
                        <select name="tipo" class="form-select" id="tipo-descuento" required>
                            <option value="porcentaje" {{ old('tipo') === 'porcentaje' ? 'selected' : '' }}>Porcentaje (%)</option>
                            <option value="monto_fijo" {{ old('tipo') === 'monto_fijo' ? 'selected' : '' }}>Monto fijo ($)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Valor</label>
                        <div class="input-group">
                            <span class="input-group-text" id="simbolo-tipo">%</span>
                            <input type="number" name="valor" class="form-control"
                                   value="{{ old('valor') }}" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Límite de usos <small class="text-muted">(vacío = ilimitado)</small></label>
                        <input type="number" name="limite_usos" class="form-control"
                               value="{{ old('limite_usos') }}" min="1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de expiración <small class="text-muted">(vacío = sin límite)</small></label>
                        <input type="date" name="fecha_expiracion" class="form-control"
                               value="{{ old('fecha_expiracion') }}">
                    </div>
                </div>

            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">Alcance del cupón</div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Aplicar a</label>
                    <select name="alcance" class="form-select" id="alcance" required>
                        <option value="tienda" {{ old('alcance') === 'tienda' ? 'selected' : '' }}>Toda la tienda</option>
                        <option value="categoria" {{ old('alcance') === 'categoria' ? 'selected' : '' }}>Una categoría</option>
                        <option value="productos" {{ old('alcance') === 'productos' ? 'selected' : '' }}>Productos específicos</option>
                    </select>
                </div>

                <div id="selector-categoria" style="display:none;">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select">
                        <option value="">Selecciona una categoría</option>
                        @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="selector-productos" style="display:none;">
                    <label class="form-label">Productos</label>
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px;">
                        @foreach($productos as $prod)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="productos[]" value="{{ $prod->id }}"
                                   id="prod-{{ $prod->id }}"
                                   {{ in_array($prod->id, old('productos', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="prod-{{ $prod->id }}">
                                {{ $prod->nombre }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">Notificación</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Enviar cupón por notificación a</label>
                    <select name="notificar" class="form-select" id="notificar">
                        <option value="">No enviar notificación</option>
                        <option value="todos">Todos los usuarios</option>
                        <option value="especifico">Un usuario específico</option>
                    </select>
                </div>
                <div id="selector-usuario" style="display:none;">
                    <label class="form-label">Usuario</label>
                    <select name="user_id" class="form-select">
                        <option value="">Selecciona un usuario</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-admin">
                <i class="bi bi-check-lg"></i> Crear cupón
            </button>
            <a href="{{ route('admin.cupones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Cambiar símbolo % / $
document.getElementById('tipo-descuento').addEventListener('change', function () {
    document.getElementById('simbolo-tipo').textContent = this.value === 'porcentaje' ? '%' : '$';
});

// Mostrar/ocultar selector según alcance
document.getElementById('alcance').addEventListener('change', function () {
    document.getElementById('selector-categoria').style.display = this.value === 'categoria' ? 'block' : 'none';
    document.getElementById('selector-productos').style.display  = this.value === 'productos'  ? 'block' : 'none';
});

// Mostrar/ocultar selector de usuario
document.getElementById('notificar').addEventListener('change', function () {
    document.getElementById('selector-usuario').style.display = this.value === 'especifico' ? 'block' : 'none';
});

// Regenerar código vía fetch
document.getElementById('btn-regenerar').addEventListener('click', function () {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let codigo = 'CAT-';
    for (let i = 0; i < 6; i++) codigo += chars.charAt(Math.floor(Math.random() * chars.length));
    document.querySelector('input[name="codigo"]').value = codigo;
});

// Restaurar estado si hay old values
const alcanceActual = document.getElementById('alcance').value;
if (alcanceActual === 'categoria') document.getElementById('selector-categoria').style.display = 'block';
if (alcanceActual === 'productos')  document.getElementById('selector-productos').style.display  = 'block';
const tipoActual = document.getElementById('tipo-descuento').value;
document.getElementById('simbolo-tipo').textContent = tipoActual === 'porcentaje' ? '%' : '$';
</script>
@endpush
@endsection