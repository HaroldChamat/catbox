<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductoAdminController;
use App\Http\Controllers\Admin\CategoriaAdminController;
use App\Http\Controllers\Admin\OrdenAdminController;
use App\Http\Controllers\Admin\EstadisticaController;
use App\Http\Controllers\DireccionController;
use App\Http\Controllers\ComentarioOrdenController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\Admin\ResenaAdminController;
use App\Http\Controllers\CuponController;
use App\Http\Controllers\Admin\CuponAdminController;

// ─────────────────────────────────────────────
// RUTAS PÚBLICAS
// ─────────────────────────────────────────────

// Landing page pública (sin login)
Route::get('/', [ProductoController::class, 'landing'])->name('landing');

// Catálogo de productos
Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/buscar', [ProductoController::class, 'buscar'])->name('productos.buscar');
Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
Route::get('/categoria/{slug}', [ProductoController::class, 'categoria'])->name('productos.categoria');

// ─────────────────────────────────────────────
// AUTH
// ─────────────────────────────────────────────
Auth::routes();

// ─────────────────────────────────────────────
// RUTAS USUARIO AUTENTICADO
// ─────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard usuario
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Carrito
    Route::prefix('carrito')->name('carrito.')->group(function () {
        Route::get('/', [CarritoController::class, 'index'])->name('index');
        Route::post('/agregar/{productoId}', [CarritoController::class, 'agregar'])->name('agregar');
        Route::put('/actualizar/{itemId}', [CarritoController::class, 'actualizar'])->name('actualizar');
        Route::delete('/eliminar/{itemId}', [CarritoController::class, 'eliminar'])->name('eliminar');
        Route::delete('/vaciar', [CarritoController::class, 'vaciar'])->name('vaciar');
    });

    // Órdenes
    Route::prefix('ordenes')->name('ordenes.')->group(function () {
        Route::get('/', [OrdenController::class, 'index'])->name('index');
        Route::get('/checkout', [OrdenController::class, 'checkout'])->name('checkout');
        Route::post('/procesar', [OrdenController::class, 'procesar'])->name('procesar');
        Route::get('/{id}', [OrdenController::class, 'show'])->name('show');
        Route::get('/{id}/confirmacion', [OrdenController::class, 'confirmacion'])->name('confirmacion');
    });

    // Direcciones de entrega
    Route::prefix('direcciones')->name('direcciones.')->group(function () {
    Route::post('/', [DireccionController::class, 'guardar'])->name('guardar');
    Route::post('/{id}/principal', [DireccionController::class, 'establecerPrincipal'])->name('principal');
    Route::delete('/{id}', [DireccionController::class, 'eliminar'])->name('eliminar');
    });

    // Rutas de órdenes
    Route::prefix('ordenes')->name('ordenes.')->group(function () {
    Route::get('/', [OrdenController::class, 'index'])->name('index');
    Route::get('/{id}', [OrdenController::class, 'show'])->name('show');
    Route::put('/{id}/actualizar-info', [OrdenController::class, 'actualizarInfo'])->name('actualizar-info');
    Route::put('/{id}/completar-pago', [OrdenController::class, 'completarPago'])->name('completar-pago');
    Route::delete('/{id}/cancelar', [OrdenController::class, 'cancelar'])->name('cancelar');
    });
    // Comentarios de órdenes (usuario)
    Route::prefix('ordenes/{ordenId}/comentarios')->name('ordenes.comentarios.')->group(function () {
        Route::post('/', [ComentarioOrdenController::class, 'guardar'])->name('guardar');
        Route::post('/marcar-leidos', [ComentarioOrdenController::class, 'marcarComoLeidos'])->name('marcar-leidos');
    });
    // Favoritos
    Route::get('/favoritos', [FavoritoController::class, 'index'])->name('favoritos.index');
    Route::post('/favoritos/{productoId}/toggle', [FavoritoController::class, 'toggle'])->name('favoritos.toggle');

    // Reseñas
    Route::post('/productos/{productoId}/resenas', [ResenaController::class, 'guardar'])->name('resenas.guardar');
    Route::put('/productos/{productoId}/resenas', [ResenaController::class, 'editar'])->name('resenas.editar');

    // Cupones
    Route::post('/carrito/cupon/aplicar', [CuponController::class, 'aplicar'])->name('cupon.aplicar');
    Route::post('/carrito/cupon/quitar', [CuponController::class, 'quitar'])->name('cupon.quitar');

    //Notificaciones
    Route::post('/notificaciones/marcar-leidas', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return response()->json(['success' => true]);
    })->name('notificaciones.marcar-leidas');
});


// ─────────────────────────────────────────────
// RUTAS ADMINISTRADOR
// ─────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard admin
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // Gestión de productos
    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/', [ProductoAdminController::class, 'index'])->name('index');
        Route::get('/crear', [ProductoAdminController::class, 'crear'])->name('crear');
        Route::post('/', [ProductoAdminController::class, 'guardar'])->name('guardar');
        Route::get('/{id}/editar', [ProductoAdminController::class, 'editar'])->name('editar');
        Route::put('/{id}', [ProductoAdminController::class, 'actualizar'])->name('actualizar');
        Route::delete('/{id}', [ProductoAdminController::class, 'eliminar'])->name('eliminar');
        Route::delete('/{id}/imagen/{imgId}', [ProductoAdminController::class, 'eliminarImagen'])->name('eliminar-imagen');
    });

    // Gestión de categorías
    Route::prefix('categorias')->name('categorias.')->group(function () {
        Route::get('/', [CategoriaAdminController::class, 'index'])->name('index');
        Route::get('/crear', [CategoriaAdminController::class, 'crear'])->name('crear');
        Route::post('/', [CategoriaAdminController::class, 'guardar'])->name('guardar');
        Route::get('/{id}/editar', [CategoriaAdminController::class, 'editar'])->name('editar');
        Route::put('/{id}', [CategoriaAdminController::class, 'actualizar'])->name('actualizar');
        Route::delete('/{id}', [CategoriaAdminController::class, 'eliminar'])->name('eliminar');
    });

    // Gestión de órdenes
    Route::prefix('ordenes')->name('ordenes.')->group(function () {
        Route::get('/', [OrdenAdminController::class, 'index'])->name('index');
        Route::get('/{id}', [OrdenAdminController::class, 'show'])->name('show');
        Route::put('/{id}/estado', [OrdenAdminController::class, 'updateEstado'])->name('updateestado');
        Route::delete('/{id}', [OrdenAdminController::class, 'destroy'])->name('destroy');
    });

    // Estadísticas
    Route::prefix('estadisticas')->name('estadisticas.')->group(function () {
        Route::get('/', [EstadisticaController::class, 'index'])->name('index');
        Route::get('/ventas', [EstadisticaController::class, 'ventas'])->name('ventas');
        Route::get('/productos', [EstadisticaController::class, 'productos'])->name('productos');
        Route::get('/clientes', [EstadisticaController::class, 'clientes'])->name('clientes');
        Route::get('/ventas-realtime', [EstadisticaController::class, 'ventasRealTime'])->name('ventas-realtime');
    });

    // RUTA TEMPORAL DE DEBUGGING - ELIMINAR DESPUÉS DE SOLUCIONAR
    Route::middleware(['auth', 'admin'])->get('/debug/producto/{id}', function($id) {
    $producto = \App\Models\Producto::with(['imagenes', 'categoria'])->findOrFail($id);
    
    return response()->json([
        'producto' => $producto,
        'fillable' => $producto->getFillable(),
        'casts' => $producto->getCasts(),
        'attributes' => $producto->getAttributes(),
        'relaciones' => [
            'imagenes_count' => $producto->imagenes->count(),
            'tiene_principal' => $producto->imagenes()->where('es_principal', true)->exists(),
            'categoria' => $producto->categoria->nombre
        ]
    ]);
    })->name('debug.producto');

    // Comentarios de órdenes (admin)
    Route::prefix('ordenes/{ordenId}/comentarios')->name('ordenes.comentarios.')->group(function () {
        Route::post('/', [ComentarioOrdenController::class, 'guardar'])->name('guardar');
        Route::post('/marcar-leidos', [ComentarioOrdenController::class, 'marcarComoLeidos'])->name('marcar-leidos');
    });
    // Moderación de reseñas
    Route::prefix('resenas')->name('resenas.')->group(function () {
        Route::get('/', [ResenaAdminController::class, 'index'])->name('index');
        Route::post('/{id}/aprobar', [ResenaAdminController::class, 'aprobar'])->name('aprobar');
        Route::post('/{id}/rechazar', [ResenaAdminController::class, 'rechazar'])->name('rechazar');
        Route::delete('/{id}', [ResenaAdminController::class, 'destruir'])->name('destruir');
    });

    // Cupones admin
    Route::prefix('cupones')->name('cupones.')->group(function () {
        Route::get('/', [CuponAdminController::class, 'index'])->name('index');
        Route::get('/crear', [CuponAdminController::class, 'crear'])->name('crear');
        Route::post('/', [CuponAdminController::class, 'guardar'])->name('guardar');
        Route::post('/{id}/toggle', [CuponAdminController::class, 'toggleActivo'])->name('toggle');
        Route::delete('/{id}', [CuponAdminController::class, 'destruir'])->name('destruir');
    });
});