<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductoAdminController;

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
});

// ─────────────────────────────────────────────
// RUTAS ADMINISTRADOR
// ─────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

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
});